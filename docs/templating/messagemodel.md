# MessageModel

Whenever you're dealing with a message in your template, you're actually working with a MessageModel object.

## Simple Output

Outputting a MessageModel object without attaching a property or method will return the message's content.

```twig
<p>{{ message }}</p>
```

## Properties

### `author`

Returns a [UserModel](https://docs.craftcms.com/api/v3/craft-elements-user.html) object representing the message's author.

### `authorId`

The message's author ID.

### `content`

The message's content.

### `dateCreated`

A [DateTime](http://php.net/manual/en/class.datetime.php) object of the date the message was created.

### `dateUpdated`

A [DateTime](http://php.net/manual/en/class.datetime.php) object of the date the message was last updated.

### `id`

The message's ID.

### `ticketId`

The message's ticket ID.
