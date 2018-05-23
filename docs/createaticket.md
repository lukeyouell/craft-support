# Create a Ticket

Creating a ticket requires a user to be logged in. You then submit to the `support/tickets/create` form action to create the ticket.

The following is an example of creating a new support ticket:

```twig
<form method="post" accept-charset="UTF-8">
  {{ csrfInput() }}
  {{ redirectInput('support/tickets') }}
  <input type="hidden" name="action" value="support/tickets/create">

  <input type="text" name="title">

  <textarea name="message" required></textarea>

  <input type="submit" value="Submit">
</form>
```

Note that both the `title` and `message` fields are required.
