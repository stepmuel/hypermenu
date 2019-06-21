# HyperMenu

HyperMenu is a JSON based format to describe interlinked menu structures. It is designed to be simple and compact, and can be used to create something that feels like a native app within minutes. You can think of it as HTML for hierarchical menus.

Some examples how HyperMenu can be used:

* Control dashboard for an embedded device.
* System status monitor and script launcher.
* File system browser.
* Cross platform app prototype.
* Well defined notation to describe menu structures.

The [HyperMenu Client for iOS](http://hypermenu.heap.ch/) is considered the reference implementation.

## Example

The following JSON object represents a menu:

```js
{
  "title": "Lights",
  "groups": [
    {
      "header": "Rooms",
      "items": [
        {"label": "Bedroom", "menu": {"request": {"url": "bedroom.json"}}},
        {"label": "Office", "menu": {"request": {"url": "office.json"}}}
      ]
    },
    {
      "items": [
        {"label": "All Off", "action": {"request": {"url": "off.php"}}}
      ]
    }
  ]
}
```

The iOS app will render it as follows:

![Example Menu](http://hypermenu.heap.ch/assets/example.png)

More examples can be found in the `menus` folder. To access them, use the builtin PHP webserver.

```sh
php -S 0.0.0.0:8080 -t menus
```

You can now access the menus using `http://laptop.local:8080/test.json`. Use `hostname` to find out your hostname.

### test.json

A simple circular menu structure with demonstrates some of the features.

### fs.php

A simple file system browser menu. Be aware that using this might expose sensitive data to the network. Use the following JSON object to access the menu using a predefined header. The hostname has to be changed accordingly.

```js
{"request": {"url": "http://laptop.local:8080/fs.php", "stickyHeaders": {"Authorization": "Bearer 106842b48d349a7f"}}}
```

### info.php

Returns infos about the request made to fetch it. Useful to debug header values set using `headers` or `stickyHeaders`.

## Specification

The full HyperMenu specification can be found in [SPECIFICATION.md](https://github.com/stepmuel/hypermenu/blob/master/README.md).

## Participate

For feedback, ideas or to show me your menus, please contact me at *stephan (at) heap.ch* or on twitter [@stepmuel](https://twitter.com/stepmuel). I am also looking for someone interested in doing an implementation for Android.
