# TicketStatusModel

Whenever you're dealing with a ticket status in your template, you're actually working with a TicketStatusModel object.

## Simple Output

Outputting a TicketStatusModel object without attaching a property or method will return the ticket status name.

```twig
<h1>{{ ticketStatus }}</h1>
```

## Properties

### `colour`

The ticket status colour.

### `cpEditUrl`

Returns the URL to the ticket status edit page within the control panel.

### `dateCreated`

A [DateTime](http://php.net/manual/en/class.datetime.php) object of the date the ticket status was created.

### `dateUpdated`

A [DateTime](http://php.net/manual/en/class.datetime.php) object of the date the ticket status was last updated.

### `default`

Whether the ticket status is set as the default.

### `handle`

The the ticket status handle.

### `id`

The ticket status ID.

### `name`

The ticket status name.

### `newMessage`

Whether the ticket status is used when a new message is posted.

### `sortOrder`

The ticket status sort order.
