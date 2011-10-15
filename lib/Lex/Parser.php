<?php
/**
 * Part of the Lex Template Parser.
 *
 * @author     Dan Horrigan
 * @license    MIT License
 * @copyright  2011 Dan Horrigan
 */

class LexParsingException extends Exception { }

class Lex_Parser
{
	protected $scope_glue = '.';
	protected $tag_regex = '';
	protected $variable_loop_regex = '';
	protected $variable_regex = '';
	protected $extractions = array(
		'noparse' => array(),
	);

	/**
	 * The main Lex parser method.  Essentially acts as dispatcher to
	 * all of the helper parser methods.
	 *
	 * @param   string        $text      Text to parse
	 * @param   array|object  $data      Array or object to use
	 * @param   mixed         $callback  Callback to use for Callback Tags
	 * @return  string
	 */
	public function parse($text, $data = array(), $callback = false)
	{
		$this->setup_regex();

		$text = $this->extract_noparse($text);
		$text = $this->parse_comments($text);
		$text = $this->parse_variables($text, $data);

		if ($callback)
		{
			$text = $this->parse_callback_tags($text, $callback);
		}

		$text = $this->inject_extractions($text);

		return $text;
	}

	/**
	 * Removes all of the comments from the text.
	 *
	 * @param   string  $text  Text to remove comments from
	 * @return  string
	 */
	public function parse_comments($text)
	{
		return preg_replace('/\{\{#.*?#\}\}/s', '', $text);
	}

	/**
	 * Recursivly parses all of the variables in the given text and
	 * returns the parsed text.
	 *
	 * @param   string        $text  Text to parse
	 * @param   array|object  $data  Array or object to use
	 * @return  string
	 */
	public function parse_variables($text, $data)
	{
		/**
		 * $data_matches[][0][0] is the raw data loop tag
		 * $data_matches[][0][1] is the offset of raw data loop tag
		 * $data_matches[][1][0] is the data variable (dot notated)
		 * $data_matches[][1][1] is the offset of data variable
		 * $data_matches[][2][0] is the content to be looped over
		 * $data_matches[][2][1] is the offset of content to be looped over
		 */
		if (preg_match_all($this->variable_loop_regex, $text, $data_matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE))
		{
			foreach ($data_matches as $index => $match)
			{
				if ($loop_data = $this->get_variable($match[1][0], $data))
				{
					$looped_text = '';
					foreach ($loop_data as $item_data)
					{
						$looped_text .= $this->parse_variables($match[2][0], $item_data);
					}
					$text = preg_replace('/'.preg_quote($match[0][0], '/').'/m', $looped_text, $text, 1);
				}
			}
		}

		/**
		 * $data_matches[0] is the raw data tag
		 * $data_matches[1] is the data variable (dot notated)
		 */
		if (preg_match_all($this->variable_regex, $text, $data_matches))
		{
			foreach ($data_matches[1] as $index => $var)
			{
				$text = str_replace($data_matches[0][$index], $this->get_variable($var, $data), $text);
			}
		}

		return $text;
	}

	/**
	 * Parses all Callback tags, and sends them through the given $callback.
	 *
	 * Array sent to callback:
	 *
	 *     array(
	 *         'full_tag' => $full_tag,
	 *         'attributes' => $attributes, // Array of attributes
	 *         'scope' => $scope, // Array to define scope
	 *         'segments' => $scopes, // Here for backwards compat with Tags
	 *         'content' => $looped_content,
	 *     )
	 *
	 * @param   string  $text      Text to parse
	 * @param   mixed   $callback  Callback to apply to each tag
	 * @return  string
	 */
	public function parse_callback_tags($text, $callback)
	{

		return $text;
	}

	/**
	 * Gets or sets the Scope Glue
	 *
	 * @param   string|null  $glue  The Scope Glue
	 * @return  string
	 */
	public function scope_glue($glue = null)
	{
		if ($glue !== null)
		{
			$this->scope_glue = $glue;
		}

		return $glue;
	}

	/**
	 * Sets up all the global regex to use the correct Scope Glue.
	 *
	 * @return  void
	 */
	protected function setup_regex()
	{
		$glue = preg_quote($this->scope_glue, '/');

		$this->noparse_regex = '/\{\{\s*noparse\s*\}\}(.*?)\{\{\s*\/noparse\s*\}\}/ms';

		$this->callback_tag_regex = '/\{\{(.*?)\}\}/';
		$this->callback_loop_tag_regex = '/\{\{(.*?)\}\}/';
		$this->variable_loop_regex = '/\{\{\s*([a-zA-Z0-9_'.$glue.']+)\s*\}\}(.*?)\{\{\s*\/\1\s*\}\}/ms';
		$this->variable_regex = '/\{\{\s*([a-zA-Z0-9_'.$glue.']+)\s*\}\}/m';
	}

	/**
	 * Removes all of the comments from the text.
	 *
	 * @return  void
	 */
	protected function extract_noparse($text)
	{
		/**
		 * $matches[][0] is the raw noparse match
		 * $matches[][1] is the noparse contents
		 */
		if (preg_match_all($this->noparse_regex, $text, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$text = $this->create_extraction('noparse', $match[0], $match[1], $text);
			}
		}

		return $text;
	}

	/**
	 * Extracts text out of the given text and replaces it with a hash which
	 * can be used to inject the extractions replacement later.
	 *
	 * @param   string  $type         Type of extraction
	 * @param   string  $extraction   The text to extract
	 * @param   string  $replacement  Text that will replace the extraction when re-injected
	 * @param   string  $text         Text to extract out of
	 * @return  string
	 */
	protected function create_extraction($type, $extraction, $replacement, $text)
	{
		$hash = md5($replacement);
		$this->extractions[$type][$hash] = $replacement;

		return str_replace($extraction, "{$type}_{$hash}", $text);
	}

	/**
	 * Injects all of the extractions.
	 *
	 * @param   string  $text  Text to inject into
	 * @return  string
	 */
	protected function inject_extractions($text)
	{
		foreach ($this->extractions as $type => $extractions)
		{
			foreach ($extractions as $hash => $replacement)
			{
				$text = str_replace("{$type}_{$hash}", $replacement, $text);
			}
		}

		return $text;
	}

	/**
	 * Takes a dot-notated key and finds the value for it in the given
	 * array or object.
	 *
	 * @param   string        $key  Dot-notated key to find
	 * @param   array|object  $data  Array or object to search
	 * @param   mixed         $default  Default value to use if not found
	 * @return  mixed
	 */
	protected function get_variable($key, $data, $default = null)
	{
		foreach (explode($this->scope_glue, $key) as $key_part)
		{
			if (is_array($data))
			{
				if ( ! array_key_exists($key_part, $data))
				{
					return $default;
				}

				$data = $data[$key_part];
			}
			elseif (is_object($data))
			{
				if ( ! isset($data->{$key_part}))
				{
					return $default;
				}

				$data = $data->{$key_part};
			}
		}

		return $data;
	}
}
