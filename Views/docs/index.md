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
