# Twig Buffers Extension

This extension allows you to define buffers into which you can insert content from another part of the template or from included, embedded or child templates.
It is similar in functionality to [Blade stacks](https://laravel.com/docs/master/blade#stacks).

## Installation

```sh
composer require ju1ius/twig-buffers-extension
```

```php

use Twig\Environment;
use ju1ius\TwigBuffersExtension\TwigBuffersExtension;

$twig = new Environment();
$twig->addExtension(new TwigBuffersExtension());
```

## Basic usage

Let's start with what will probably be the most frequent use case.

Given the following templates:
```twig
{# base.html.twig #}
<head>
  <link rel="stylesheet" href="main.css">
  {% buffer stylesheets %}
</head>
<body>
  <main>
    {% block content '' %}
  </main>
  <script src="main.js"></script>
  {% buffer scripts %}
</body>
```

```twig
{# page.html.twig #}

{% extends 'base.html.twig' %}

{% block content %}

  {{ greeting }}

  {% append to stylesheets %}
    <link rel="stylesheet" href="page.css">
  {% endappend %}
  {% append to scripts %}
    <script src="page.js"></script>
  {% endappend %}

{% endblock content %}

```
Rendering page.html.twig
```php
$twig->display('page.html.twig', ['greeting' => 'Hello buffers!']);
```
Will output:
```html
<head>
  <link rel="stylesheet" href="main.css">
  <link rel="stylesheet" href="page.css">
</head>
<body>
  <main>
    Hello buffers!
  </main>
  <script src="main.js"></script>
  <script src="page.js"></script>
</body>
```

## The buffer tag

The buffer tag does two things:
  * it opens a named buffer if a buffer of the same name doesn't exist.
  * it references the named buffer so that it's content can be displayed
    at the tag's location.

A named buffer can therefore be referenced several times:

```twig
<p>{% buffer my_buffer %}</p>
<p>{% buffer my_buffer %}</p>
{% append to my_buffer 'bar' %}
```
```html
<p>bar</p>
<p>bar</p>
```

By default, the contents of the buffer are joined using the empty string,
but you can customize the joining string:
```twig
<p>{% buffer my_buffer joined by ', ' %}</p>
{% append to my_buffer 'foo' %}
{% append to my_buffer 'bar' %}
{% append to my_buffer 'baz' %}
```
```html
<p>foo, bar, baz</p>
```
You can also provide a «final glue», similarly to the `join` twig filter:
```twig
<p>{% buffer my_buffer joined by '<br>', '<hr>' %}</p>
{% append to my_buffer 'foo' %}
{% append to my_buffer 'bar' %}
{% append to my_buffer 'baz' %}
```
```html
<p>foo<br>bar<hr>baz</p>
```
As we just saw, even when using automatic escaping,
the joining strings _will not be automatically escaped_.
You'll have to escape them yourself if they come from untrusted sources:
```twig
{% buffer my_buffer joined by some_variable|escape('html') %}
```


## Inserting content into buffers

This is done via the `append` and `prepend` tags.
They share the same syntax apart from their respective tag names.

As with the `block` tag, you can use a short or a long syntax:
```twig
{# short syntax #}
{% append to my_buffer 'Some content' %}
{# long syntax #}
{% append to my_buffer %}
  Some other content
{% endappend %}
```

As the name implies, the `append` tag appends content to the buffer.

The `prepend` tag however, doesn't prepend content to the buffer,
but instead *appends content to the head of the buffer*:

```twig
<p>{% buffer my_buffer %}</p>
{% append to my_buffer '1' %}
{% append to my_buffer '2' %}
{% prepend to my_buffer '3' %}
{% prepend to my_buffer '4' %}
```
```html
<p>3412</p>
```

Trying to insert content into a buffer that does not exist or is not
[in scope](#the-scope-of-a-buffer) will throw an `UnknownBuffer` exception.


### Inserting to potentially undefined buffers

If you want to insert content into a buffer
that may not exist or may not be [in scope](#the-scope-of-a-buffer),
you have two solutions:

1.
    ```twig
    {% append or ignore to my_buffer '...' %}
    ```
    If `my_buffer` doesn't exist or is not [in scope](#the-scope-of-a-buffer),
    this is a no-op.

2.
    ```twig
    {% append or create to my_buffer '...' %}
    ```
    If `my_buffer` doesn't exist or is not [in scope](#the-scope-of-a-buffer),
    first open the buffer, then insert content into it.


### Unique insertions

You can tag insertions with a unique id in order to
prevent the same content to be inserted more than once.

```twig
{% macro search_button(text) %}
  <button aria-controls="search-dialog">{{ text }}</button>
  {% append to dialogs as search_dialog %}
    <dialog id="search-dialog">...</dialog>
  {% endappend %}
{% endmacro %}

<header>
  {{ _self.search_button('Search from header') }}
</header>
<footer>
  {{ _self.search_button('Search from footer') }}
</footer>
{% buffer dialogs %}
```

```html
<header>
  <button aria-controls="search-dialog">Search from header</button>
</header>
<footer>
  <button aria-controls="search-dialog">Search from footer</button>
</footer>
<dialog id="search-dialog">...</dialog>
```

## Clearing the contents of a buffer
You can use the `clear_buffer` function:
```twig
{% buffer my_buffer %}
{% append to my_buffer 'some content' %}
{% do clear_buffer('my_buffer') %}
```

Attempting to clear the content of a buffer that does not exist or is not
[in scope](#the-scope-of-a-buffer) will throw an `UnknownBuffer` exception.


## Checking if a buffer exists
You can use the `buffer` test:
```twig
{% if 'my_buffer' is buffer %}
  {% append to my_buffer 'some content' %}
{% endif %}
```
The `buffer` test will return `true` if
the buffer exists and is [in scope](#the-scope-of-a-buffer).

## Checking if a buffer is empty
You can use the `empty_buffer` test:
```twig
{% buffer my_buffer %}
My buffer is {{ 'my_buffer' is empty_buffer ? 'empty' : 'not empty' }}!
```
Or for a more practical example:
```twig
{% if 'my_buffer' is not empty_buffer %}
  <div>
    {% buffer my_buffer %}
  </div>
{% endif %}
```
The `empty_buffer` test will return `true` if:
  * the buffer exists and is empty
  * the buffer doesn't exist or is not [in scope](#the-scope-of-a-buffer).


## The scope of a buffer

When a template contains a `{% buffer %}` tag,
the corresponding buffers are opened as soon as the template begins to render.

Once opened, a buffer remains available until the end of the topmost render call.

Therefore, a buffer is available:
  1. in the whole template it is referenced in
     and in all it's included, embedded or child templates.
  2. in all subsequent siblings of the template it is referenced in.

To clarify, lets look at some examples.

The following works because of rule n°1:
```twig
{% append to my_buffer '' %}
{% buffer my_buffer %}
```
The following also works because of rule n°1:
```twig
{# partial: insert-into-buffer.html.twig #}
{% append to my_buffer 'some content' %}
```
```twig
{% include 'insert-into-buffer.html.twig' %}
{% buffer my_buffer %}
```
The following works because of rule n°2:
```twig
{# partial: reference-buffer.html.twig #}
{% buffer my_buffer %}
```
```twig
{% include 'reference-buffer.html.twig' %}
{% append to my_buffer 'some content' %}
```
However, because of rule n°2, the following does not work:
```twig
{#
  This throws an `UnknownBuffer` exception beacause `my_buffer`
  is not in scope until the 'reference-buffer.html.twig'
  partial is included.
#}
{% append to my_buffer 'some content' %}
{% include 'reference-buffer.html.twig' %}
```
