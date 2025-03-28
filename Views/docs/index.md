---
title: My Document Title
description: This is a description of my document.
keywords: markdown, metadata, tutorial
#author: John Doe
#date: 2024-05-17
---

# This is a Markdown Page

Here is a markdown page example

If you want to add copy to clipboard functionality to your markdown, for example for code blocks, go to vendor/erusev/Parsedown.php and find `$class = 'language-'.$language` line 446 and replace it with this line ``` php $class = 'language-'.$language . ' c0py'; ```. You can repeat for other tags too.

## Available vars

For non-api called (not /api/) the following variables are exposed on each request

- $usernameArray - It is an array with all the data of the currently logged in user ike id, username, role, email, theme and etc. If not logged in - empty array []
- $isAdmin - A boolean whether the currently logged in user is an Administrator. Not logged in - false, not an admin role also false
- $theme - It reads the $usernameArray['theme'] key. If not available, defaults to COLOR_SCHEME constant in /config/site-settings.php

For api calls:

- All of the above
- $loginInfo - an Array holding all the other variables
- $loginInfo - an Array holding the values of $usernameArray, $isAdmin, $loggedIn, $theme in their respective keys. I.e $loginInfo['usernameArray'].
