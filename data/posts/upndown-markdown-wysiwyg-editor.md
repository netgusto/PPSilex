---
title: Markdown wysiwyg editor
slug: upndown-markdown-wysiwyg-editor
author: Jérôme Schneider
date: 2014-06-10 21:00
status: publish

image: stairs.jpg
about: [markdown, upndown, open-source]
---
Discover **upndown**, the Markdown to HTML converter.

We recently unveiled **upndown**, an HTML to Markdown javascript library, for Nodejs and the browser.

**upndown** is meant as a low-level component allowing for easy implementation of Markdown WYSIWYG editors.

The source code is available under the MIT Licence.

## Demo

[Demo of a simple yet functionnal implementation here.](http://upndown.netgusto.com/)

## What it does

**upndown** is designed to offer a fast, reliable and whitespace perfect conversion for HTML documents that are made up of elements that have an equivalent in the Markdown syntax, making it suited for Markdown WYSIWYG editors.

## How to use

**Use upndown with standard JS**

```html
<script type="text/javascript" src="/lib/htmlparser.min.js"></script>
<script type="text/javascript" src="/lib/upndown.min.js"></script>
<script type="text/javascript">

    var und = new upndown();
    var markdown = und.convert('<h1>Hello, World !</h1>');

    console.log(markdown); // Outputs: # Hello, World !

</script>
```

**Use upndown with RequireJS**

```html
<script type="text/javascript" src="/path/to/require.js"></script>
<script type="text/javascript">

require.config({
    paths: {
        'upndown': '/assets/upndown/lib/upndown.min'
        'htmlparser': '/assets/upndown/lib/htmlparser.min'
    }
});

require(['upndown'], function(upndown) {
    var und = new upndown();
    var markdown = und.convert('<h1>Hello, World !</h1>');

    console.log(markdown); // Outputs: # Hello, World !
});
</script>
```

**Server side, with NodeJS**

```javascript
var upndown = require('upndown');

var und = new upndown();
var markdown = und.convert('<h1>Hello, World !</h1>');

console.log(markdown); // Outputs: # Hello, World !
```

## How it works

The HTML source code is converted to a dom tree using the DOM in the browser, and using jsdom on nodejs. The DOM tree is then walked and parsed by upndown to project a Markdown equivalent for every HTML element in the DOM, resulting in a clean, fresh Markdown document.