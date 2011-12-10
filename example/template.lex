<!DOCTYPE html>
<html>
	<head>
		<title>{{title}}</title>
	</head>
	<body>

		<?php echo 'PHP is not parsed, unless you explicitly tell it to.' ?>

		<h1>Hello, {{ name }}!</h1>

	{{ if show_real_name }}
		<p>My name is {{real_name.first}} {{real_name.last}}</p>
	{{ else }}
		<p>My name is John Doe</p>
	{{ endif }}

		<ul id="navigation">
			{{template:partial name="navigation" group=nav_group}}

			{{/template:partial}}
		</ul>

		{{ leftover:stuff }}
			{{ if baz == 'baz' }} Foo {{ endif }}
		{{ /leftover:stuff }}

		<h2>Projects</h2>

		{{ projects }}
			<h3>{{ em value=name }}</h3>
			<h4>Contributors</h4>
			<ul>
			{{ contributors }}
				<li>{{ name }}</li>
			{{ /contributors }}
			</ul>
		{{ /projects }}

		<p>Example:</p>

		{{ noparse }}
		<pre>Hello, {{ name }}!</pre>
		{{ /noparse }}

	</body>
</html>
