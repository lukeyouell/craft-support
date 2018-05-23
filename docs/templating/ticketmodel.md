# TicketModel

Whenever you're dealing with a ticket in your template, you're actually working with a TicketModel object.

## Simple Output

Outputting a TicketModel object without attaching a property or method will return the ticket's title.

```twig
<h1>{{ ticket }}</h1>
```

## Properties

### `author`

Returns a [UserModel](https://docs.craftcms.com/v2/templating/usermodel.html) object representing the ticket's author.

### `authorId`

The ticket's author ID.

### `cpEditUrl`

Returns the URL to the ticket's edit page within the control panel.

### `dateCreated`

A [DateTime](https://docs.craftcms.com/v2/templating/datetime.html) object of the date the ticket was created.

### `dateUpdated`

A [DateTime](https://docs.craftcms.com/v2/templating/datetime.html) object of the date the ticket was last updated.

### `id`

The ticket's ID.

### `messages`

Returns a [MessageModel](messagemodel.md) object representing the ticket's messages.

### `ticketStatus`

Returns a [TicketStatusModel](ticketstatusmodel.md) object representing the ticket's status.

### `ticketStatusId`

The ticket's status ID.
