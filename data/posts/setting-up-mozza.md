---
title: Setting up Mozza
author: Jérôme Schneider
date: 2014-06-10 14:00
status: publish

image: jet-sky.jpg
about: [life, universe, everything]
comments: on
---
Let's start this thing.

**Mozza** is a deliciously simple [Markdown](http://daringfireball.net/projects/markdown/) blog system, based on [Silex](http://silex.sensiolabs.org).

Mozza is designed to be an ascetic solution to online post publication, rather than a do-it-all piece of software.

Posts are Markdown files, stored in a folder of the website (`data/posts`). To add a post, simply create a file in this folder.

## What to do next ?

1. Edit the file `app/parameters.yml`, and configure your blog (your name, twitter account, etc.)

2. Create your first post by adding a file in `data/posts/`
    
    1. The file name could be anything, but it has to end with `.md`

    2. To define your post metadata, like it's date, or title, you'll have to use the yaml front matter notation; have a look at `hello-world.md` to see how it's done

3. Once you created your first post, remove the default posts by deleting the files `data/posts/hello-world.md` and `data/posts/setting-up-mozza.md`.


```php
#
# Amazon S3, if enabled
#

if(array_key_exists('storage', $app['config']) && $app['config']['storage']['engine'] == 's3') {
    $credentials = new Credentials(
        $app['config']['storage']['s3']['aws_access_key_id'],
        $app['config']['storage']['s3']['aws_secret_access_key']
    );
    $s3Client = S3Client::factory(array(
        'credentials' => $credentials
    ));

    $command = $s3Client->getCommand('ListObjects', array('Bucket' => $app['config']['storage']['s3']['bucket']));
    $command->set('MaxKeys', 50);
    $result = $command->getResult();
    var_dump($result);
}