# Pulpy

Deliciously simple Markdown blog system, based on Silex.

## Demo

Net Gusto's blog uses Pulpy: <http://blog.netgusto.com/>

## Install

The installation requires composer. Get it at <http://getcomposer.org>

```bash
$ cd /path/to/www
$ git clone https://github.com/netgusto/Pulpy.git
$ cd Pulpy
$ composer install
```

## Test-run the application

To test-run Pulpy, you may just use the PHP built-in server, using these commands (requires PHP 5.4+):

```bash
$ cd /path/to/www/Pulpy
$ php -S 0.0.0.0:8000 -t web web/index.php
```

And the head up to http://localhost:8000 in your browser.

## Host the application

Any web server capable of running PHP 5.3+ might work.

For Apache, here's the minimum configuration to provide (it's already applied by a .htaccess file present in `web/`):

```apacheconf
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L]

php_flag display_errors Off
php_flag short_open_tag Off

SetOutputFilter DEFLATE
```

Note for Apache: `Options +FollowSymLinks` has to be enabled in your virtualhost.

## Parameters

Upon install a file named `parameters.yml` has been created for you in the `app/` folder. It contains default value for all the parameters you can adjust.

The default parameters are:

```yaml
site:
    title: My Pulpy blog                        # The name of your blog; required                
    description: "Stuff about everything"       # A short description of this blog; required
    theme: netgusto/pulpy-theme-medium          # the package name of the theme; required try also netgusto/pulpy-theme-dropplets
    locale: fr_FR.UTF-8                         # The server locale; required
    owner:
        name: Me                                # Your full name, or your company name; required
        twitter: GetPulpy                       # Your twitter username; required
        mail: pulpy@netgusto.com                # Your email; required
        website: http://netgusto.com            # Your website URL (not this blog); required
    about:
        slug: about                             # the slug of a post used for your About page; optional

date:
    timezone: Europe/Paris                      # Timezone, required
    format: "F jS, Y"                           # Date Format as explained here: http://php.net/date

components:
    
    disqus:
        shortname: pulpyblog                    # the Disqus ID for your blog; optional

    googleanalytics:
        uacode: UA-XXXXXXXX-X                   # the Google analytic UA code; optional
```

## Usage

Posts are Markdown files, ending in `.md`, stored in the `data/posts` folder.

To define metadata such as a date, author, status, etc. in your post, you'll have to use the yaml front matter notation.

Example of such a post (`data/posts/about.md`):

```markdown
---
title: About this blog          # The title of the post; required
slug: about                     # The url of the post; optional, by default set to the file name
author: Jérôme Schneider        # The name of the author; optional, by default, uses site.owner.name in the config
date: 2014-06-01 12:00          # The date of the post; required
status: publish                 # The status of the post (draft, publish); optional, publish by default

image: desk.jpg                 # The image associated with the post; required, path relative to data/res
about: [this blog, pulpy]       # An array of categories; optional; not all themes use this
comments: off                   # Wether or not to display the comment form; optional; not all themes support comments
---
Stuffed with Markdown flavoured **Pulpy**.

At Net Gusto, we love [**Markdown**](http://daringfireball.net/projects/markdown/), so much even that we placed it at the center of our work processes. We like it's *simplicity*, its *readability*, its *universalism*.

When we write a document, we write it with Markdown.

Same goes with blog posts, why not ? So we needed a simple tool that would allow us to publish blog posts simply by creating a Markdown document.
```
