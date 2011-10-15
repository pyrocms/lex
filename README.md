Lex
===

Lex is a lightweight template parser.

**This is still under heavy development.**


_Lex is released under the MIT License and is Copyrighted 2011 Dan Horrigan._

Basic Usage
===========

Including Lex
-------------

Lex includes a basic autoloader to load it's classes.

    include 'lib/Lex/Autoloader.php';
    Lex_Autoloader::register();

Using Lex
---------

Basic parsing of a file:

    $parser = new Lex_Parser();
    $template = $parser->parse(file_get_contents('template.lex'), $data);

You can also set the Scope Glue (see "Scope Glue" under Syntax below):

    $parser = new Lex_Parser();
    $parser->scope_glue(':');
    $template = $parser->parse(file_get_contents('template.lex'), $data);

If you only want to parse a data array and not worry about callback tags or comments, you can do use the `parse_variables()` method:

    $parser = new Lex_Parser();
    $template = $parser->parse_variables(file_get_contents('template.lex'), $data);


Syntax
======

General
-------

All Lex code is delimeted by double curly braces (`{{ }}`).  These delimeters were chosen to reduce the chance of conflicts with JavaScript and CSS.

Here is an example of some Lex template code:

    Hello, {{name}}


Scope Glue
----------

Scope Glue is/are the character(s) used by Lex to trigger a scope change.  A scope change is what happens when, for instance, you are accessing a nested variable inside and array/object, or when scoping a custom callback tag.

By default a dot (`.`) is used as the Scope Glue, although you can select any character(s).


Whitespace
----------

Whitespace before or after the delimeters is allowed, however, in certain cases, whitespace within the tag is prohibited (explained in the following sections).

Some valid examples:

    {{ name }}
    {{name }}
    {{ name}}
    {{  name  }}
    {{
      name
    }}

Some invalid examples:

    {{ na me }}
    { {name} }


Comments
--------

You can add comments to your templates by wrapping the text in `{{# #}}`.

    {{# This will not bt parsed or shown in the resulting HTML #}}

    {{#
        They can be multi-line too.
    #}}


Variable Tags
-------------

When dealing with variables, you can: access single variables, access deeply nested variables inside arrays/objects, and loop over an array.  You can even loop over nested arrays.

### Simple Variable Tags

For our basic examples, lets assume you have the following array of variables (sent to the parser):

    array(
        'title'     => 'Lex is Awesome!',
        'name'      => 'World',
        'real_name' => array(
            'first' => 'Lex',
            'last'  => 'Luther',
        )
    )

Basic Example:

	{{# Parsed: Hello, World! #}}
    Hello, {{ name }}!

	{{# Parsed: <h1>Lex is Awesome!</h1> #}}
    <h1>{{ title }}</h1>

	{{# Parsed: My real name is Lex Luther!</h1> #}}
    My real name is {{ real_name.first }} {{ real_name.last }}

The `{{ real_name.first }}` and `{{ real_name.last }}` tags check if `real_name` exists, then check if `first` and `last` respectively exist inside the `real_name` array/object then returns it.

### Looped Variable Tags

Looped Variable tags are just like Simple Variable tags, except they correspond to an array of arrays/objects, which is looped over.

A Looped Variable tag is a closed tag which wraps the looped content.  The closing tag must match the opening tag exactly, except it must be prefixed with a forward slash (`/`).  There can be **no** whitespace between the forward slash and the tag name (whitespace before the forward slash is allowed).

Valid Example:

    {{ projects }} Some Content Here {{ /projects }}

Invalid Example:

    {{ projects }} Some Content Here {{/ projects }}

The looped content is what is contained between the opening and closing tags.  This content is looped through and outputted for every item in the looped array.

When in a Looped Tag you have access to any sub-variables for the current element in the loop.

In the following example, let's assume you have the following array/object of variables:

    array(
        'title'     => 'Current Projects',
        'projects'  => array(
            array(
                'name' => 'Acme Site',
                'assignees' => array(
                    array('name' => 'Dan'),
                    array('name' => 'Phil'),
                ),
            ),
            array(
                'name' => 'Lex',
                'contributors' => array(
                    array('name' => 'Dan'),
                ),
            ),
        ),
    )

In the template, we will want to display the title, followed by a list of projects and their assignees.

    <h1>{{ title }}</h1>
    {{ projects }}
        <h3>{{ name }}</h3>
        <h4>Assignees</h4>
        <ul>
        {{ assignees }}
            <li>{{ name }}</li>
        {{ /assignees }}
        </ul>
    {{ /projects }}

As you can see inside each project element we have access to that project's assignees.  You can also see that you can loop over sub-values, exactly like you can any other array.

Callback Tags
-------------

Coming Soon...
