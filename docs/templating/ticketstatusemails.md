# Ticket Status Emails

### Overview

In addition to using ticket statuses to manage tickets, you can choose emails that will be sent when an order moves into that status.

For example you might create an email called 'Ticket Confirmation for Author' which confirms the ticket has been submitted and is sent to the author. This email should likely be linked to the default ticket status, since we want it to trigger when the ticket is submitted.

Another email could be set up called 'Ticket Notification for Admin' which could also be attached to the default ticket status, but get's sent to the administrator's email address.

### Settings

Before setting up emails for Craft Support ensure that your Craft CMS installation has email configured correctly.

You can set up your email gateway in the Craft CMS control panel by going to `Settings > Email`.

If you want to have a different 'From Email' and 'From Name' for your support emails, which overrides the Craft CMS email defaults, go to `Support > Settings > General Settings` and enter your support email address and from name.

### Creating an Email

To create an email go to 'Support > Settings > Emails' and click 'New Email'.

Emails have the following configuration settings:

#### Name

Enter the name of this email as it will be shown when managing it in the control panel.

#### Email Subject

The subject of the email.

Plain text & emojis can be entered or twig can also be used to set dynamic ticket values. These values are available in the form of a [Ticket Model](templating/ticketmodel.md)

For example, for the subject we might use a template like:

```twig
[📥 New Support Ticket] {title} (#{id})
```

#### Recipient

The 'to' address or addresses for this email.

If 'Send to the author' is selected, the email will only be sent to the author of the ticket.

If 'Send to custom recipient' is selected, a list of comma-separated email addresses can be entered.

#### BCC'd Recipient

The `BCC` addresses for this email. A list of comma-separated email addresses can be entered.

#### HTML Email Template Path

The template path to a template in your site templates folder.

You can use [Ticket Model](templating/ticketmodel.md) variables in the template file.

For example, to display the ticket ID we would use:

```twig
{{ ticket.id }}
```
