---
title: Hello, World !
slug: hello-world
author: Jérôme Schneider
date: 2014-06-08 14:00
status: publish
about: [life, death, universe, everything]
---
Welcome to **Mozza**.

**Mozza** is a deliciously simple [Markdown](http://daringfireball.net/projects/markdown/) blog system, based on [Silex](http://silex.sensiolabs.org).

Mozza is designed to be an ascetic solution to online post publication, rather than a do-it-all piece of software.

Posts are Markdown files, stored in a folder of the website (`app/posts`). To add a post, simply create a file in this folder.

You can add code blocks easily using fenced code blocks. Looks like this:

```javascript
// Excerpt from the upndown library documentation
// Get it at http://upndown.netgusto.com/

// 1. Require the lib
var upndown = require('upndown');

// 2. Convert the markup
var und = new upndown();
var markdown = und.convert('<h1>Hello, World !</h1>');

// 3. Display results
console.log(markdown); // Outputs: # Hello, World !
```