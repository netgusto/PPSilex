---
title: Hello, World !
slug: hello-world
author: Jérôme Schneider
date: 2014-06-08 14:00
status: publish

image: netgusto.png
about: [life, universe, everything]
comments: off
---
Welcome to **Mozza**.

**Mozza** is a deliciously simple [Markdown](http://daringfireball.net/projects/markdown/) blog system, based on [Silex](http://silex.sensiolabs.org).

Mozza is designed to be an ascetic solution to online post publication, rather than a do-it-all piece of software.

Posts are Markdown files, stored in a folder of the website (`data/posts`). To add a post, simply create a file in this folder.

## What to do next ?

1. Edit the file `app/parameters.yml`, and configure your blog (your name, twitter account, etc.)

2. Create your first post by adding a file in `data/posts/`
    
    1. The file name could be anything, but it has to end with `.md`

    2. To define your post metadata, like it's date, or title, you'll have to use the yaml front matter notation; have a look at `hello-world.md` to see how it's done

3. Once you created your first post, remove this default post by deleting the file `data/posts/hello-world.md`