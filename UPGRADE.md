Upgrading to Lex
================

**This may change, as Lex is still under heavy development**

If you are upgrading your templates from 'Tags' or 'SimpleTags', please read below.

This is not a list of all of the new features of Lex, it is simply a guide for upgrading your templates to work with Lex.

New Delimeters
--------------

The delimeters in Lex are two (2) braces(`{{ }}`), not one (1) (`{ }`).  You will need to change all of your tags to use the new style.

Example:

    {{name}}

Whitespace in Tags
------------------

You can now put whitespace in your tags before and after the delimeters.  This goes for any type of tag: variable, conditional, etc.

Example

    {{ name }}

Variables in Conditionals
-------------------------

Variables in conditionals do not, and should not, be wrapped in delimeters.

Likewise, if the variable returns a string, you do not have to surround it with quotes.

**Old style:**

    {if '{{name}}' == 'Dan'}

**New style:**

    {{ if name == 'Dan' }}
