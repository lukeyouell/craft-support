# MessageModel

Whenever you're dealing with a message in your template, you're actually working with a MessageModel object.

## Simple Output

Outputting a MessageModel object without attaching a property or method will return the message's content.

```twig
<h1>{{ message }}</h1>
```

## Properties

### `author`

Returns a [UserModel](https://docs.craftcms.com/v2/templating/usermodel.html) object representing the message's author.

### `authorId`

The message's author ID.

### `content`

The message's content.

### `dateCreated`

A [DateTime](https://docs.craftcms.com/v2/templating/datetime.html) object of the date the message was created.

### `dateUpdated`

A [DateTime](https://docs.craftcms.com/v2/templating/datetime.html) object of the date the message was last updated.

### `id`

The message's ID.

### `ticketId`

The message's ticket ID.
