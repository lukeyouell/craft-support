# craft.support.tickets

### How to get tickets

You can access your site's tickets from your templates via `craft.support.tickets`

```twig
{% set tickets = craft.support.tickets.all() %}

{% for ticket in tickets %}
  {{ ticket.id }} - {{ ticket.name }}
{% endfor %}
```

### Parameters

`craft.support.tickets` supports the following parameters:

#### `author`

A [User Model]([UserModel](https://docs.craftcms.com/api/v3/craft-elements-user.html)) can be passed to get tickets for that user only.

#### `authorId`

Get tickets for that author only.

#### `ticketStatus`

A [Ticket Status Model](templating/ticketstatusmodel.md) can be passed to get tickets for that ticket status only.

#### `ticketStatusId`

Get tickets for that ticket status only.
