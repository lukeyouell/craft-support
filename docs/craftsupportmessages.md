# craft.support.messages

### How to get messages

You can access your site's messages from your templates via `craft.support.messages`

```twig
{% set tickets = craft.support.messages.all() %}

{% for message in messages %}
  {{ message.content }}
{% endfor %}
```

### Parameters

`craft.support.messages` supports the following parameters:

#### `author`

A [User Model]([UserModel](https://docs.craftcms.com/api/v3/craft-elements-user.html)) can be passed to get messages for that user only.

#### `authorId`

Get messages for that author only.

#### `ticketId`

Get messages for that ticket only.
