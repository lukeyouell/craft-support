# Post a Message

Posting a message requires a user to be logged in. You then submit to the `support/messages/new-message` form action to post the message.

The following is an example of posting a new message:

```twig
<form method="post" accept-charset="UTF-8">
  {{ csrfInput() }}
  <input type="hidden" name="action" value="support/messages/new-message">
  <input type="hidden" name="ticketId" value="{{ ticket.id|hash }}">

  <textarea name="message" placeholder="Post a message"></textarea>

  <input type="submit" value="Post message">
</form>
```

Note that both the `ticketId` and `message` fields are required.

The `ticketId` field must contain a valid ticket ID that is hashed to prevent tampering.
