---
title: Hello, World
slug: hello-world
author: Jérôme Schneider
date: 2014-06-10 21:00
status: publish

image: desk.jpg
about: [life, universe, everything]
comments: off
---
Welcome to **Mozza**.

**Mozza** is a deliciously simple [Markdown](http://daringfireball.net/projects/markdown/) blog system, based on [Silex](http://silex.sensiolabs.org).

Mozza is designed to be an ascetic solution to online post publication, rather than a do-it-all piece of software.

Posts are Markdown files, stored in a folder of the website (`data/posts`). To add a post, simply create a file in this folder.

![](res/mozza-logo.png)
*This is the looon image legend This is the looon image legend This is the looon image legend This is the looon image legend This is the looon image legend*


![](res/desk.jpg)

## What to do next ?

1. Edit the file `app/parameters.yml`, and configure your blog (your name, twitter account, etc.)

2. Create your first post by adding a file in `data/posts/`
    
    1. The file name could be anything, but it has to end with `.md`

    2. To define your post metadata, like it's date, or title, you'll have to use the yaml front matter notation; have a look at `hello-world.md` to see how it's done

3. Once you created your first post, remove the default posts by deleting the files `data/posts/hello-world.md` and `data/posts/setting-up-mozza.md`.

# Le code block title

```php
# Building the realpathes for configures pathes

$webdir = ROOT_DIR . '/web';
$app['abspath'] = array(
    'root' => ROOT_DIR,
    'posts' => ROOT_DIR . '/' . trim($app['config']['posts']['dir'], '/'),
    'customhtml' => ROOT_DIR . '/app/customhtml/',
    'web' => $webdir,
    'theme' => $webdir . '/vendor/' . $app['config']['site']['theme'],
    'postsresources' => $webdir . '/' . trim($app['config']['posts']['webresdir'], '/'),
    'app' => ROOT_DIR . '/app',
    'cache' => ROOT_DIR . '/app/cache',
    'source' => ROOT_DIR . '/src',
    'cachedbproxies' => ROOT_DIR . '/app/cache/db/proxies',
    'cachedbsqlite' => ROOT_DIR . '/app/cache/db/cache.db',
);

# Setting server timezone and locale
date_default_timezone_set($app['config']['date']['timezone']);
setlocale(LC_ALL, $app['config']['site']['locale']);
$app['timezone'] = new \DateTimeZone($app['config']['date']['timezone']);
```