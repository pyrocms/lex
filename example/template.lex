<!DOCTYPE html>
<html>
	<head>
		<title>{{title}}</title>
	</head>
	<body>

		<h1>Hello, {{ name }}!</h1>

		<p>My real name is {{real_name.first}} {{real_name.last}}</p>

		<ul id="navigation">
			{{template:partial name="navigation" group="{{nav_group}}"}}

			{{/template:partial}}
		</ul>

		<h2>Projects</h2>
		{{ projects }}
			<h3>{{ name }}</h3>
			<h4>Contributors</h4>
			<ul>
			{{ contributors }}
				<li>{{ name }}</li>
			{{ /contributors }}
			</ul>
		{{ /projects }}
	</body>
</html>
