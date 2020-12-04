RocketChat plugin for Kanboard
==============================

Note: The [original RocketChat plugin repository is declared "Not Maintained"](https://github.com/kanboard/plugin-rocketchat). This fork is maintained, has been *kind of* [accepted as the *official* replacement](https://github.com/kanboard/website/pull/255#event-4071181618) and is listed in the [Kanboard plugin marketplace](https://kanboard.org/plugins.html).

Receive Kanboard notifications on [RocketChat](https://rocket.chat/).

![notifications](https://user-images.githubusercontent.com/953989/101069206-f9136b80-3599-11eb-8e7d-ffffe1c29b11.png)

You can configure RocketChat notifications for a project or for each individual Kanboard user.

Author
------

- Frédéric Guillot
- Olivier Maridat
- License MIT

Requirements
------------

- Kanboard >= 1.0.37
- RocketChat

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/RocketChat`
3. Clone this repository into the folder `plugins/RocketChat`

Note: Plugin folder is case-sensitive.

Configuration
-------------

### RocketChat configuration

- Generate a new webhook url
- Go to **Administration > Integrations > New Integration > Incoming Webhook**
- You can override the channel later if required

### Kanboard configuration

#### Individual notifications

1. Copy and paste the webhook url into **Integrations > RocketChat** in your
   user profile 
2. Enable RocketChat notifications in your user profile or project settings
3. Enjoy!

#### Project notification

1. Copy and paste the webhook url into **Integrations > RocketChat** in the
   project settings
2. Add the channel name (Optional)
3. Enable RocketChat notification in the project
4. Enjoy!
