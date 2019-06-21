# HyperMenu Specification

A HyperMenu always starts with a root object of type `Menu`. A *menu* can be thought of as a *screen*, which contains *items* that are enclosed in *groups*. An `Item` can contain another menu, a different *action* or simply represent information.

Menus and actions might contain `Request` objects which describe HTTP requests. The menu or action objects will be replaced by the result of that request. Those requests enable:

* Menu hierarchies can either be a single JSON object (inline) or divided at will, even spread across multiple servers.
* Actions can trigger events on remote servers, and the servers can give feedback.

## Type Definitions

The different object types are represented as Swift classes. All properties other than `Request.url` are optional.

```swift
class Menu {
  var request: Request?
  var title: String?
  var groups: [Group]?
}
```

* Represents a menu screen.
* `title` is used as the screen title.
* If no title is given, the label of the enclosing item will be used (if available).
* The whole `Menu` object is replaced by the result of `request`.
* Requests returning a menu with another request can be used to implement slow-polling.
* Implementations might show a *loading indicator* while waiting for the first request, if `groups` doesn't exist.
* Implementations might provide a *reload* function if `request` exists.
* Implementations might cancel an ongoing request when leaving the menu (*back button*).

```swift
class Group {
  var header: String?
  var items: [Item]?
}
```

* Used to group items into different *sections*.
* `header` is used as the section title.
* To not use sections, put all elements into a single group with no header.

```swift
class Item {
  var label: String?
  var detail: String?
  var destructive: Bool?
  var action: Action?
  var menu: Menu?
  var web: String?
  var file: Request?
}
```

* Represents a menu item.
* `label` is used as the title of the item. `detail` adds secondary text.
* `action`, `menu`, `web`, or `file` define what happens if the item is selected (*selectable properties*).
  * Only one of those options can be used at once.
  * When multiple properties are given, all but the first available one are ignored (in the order listed here).
* `action` items will evaluate the associated `Action` object when selected.
  * `detail` is ignored for action items.
  * Implementations might allow multiple actions to be triggered simultaneously (before the previous action finishes).
  * Implementations might visually highlight `destructive` actions (e.g. with red color).
* `menu` items will navigate to the given menu when selected.
* `web` items will navigate to the given URL when selected.
  * URL needs to be absolute.
  * Implementations might open URL in a browser or in a specialized app.
  * Might not be implemented by all HyperMenu clients.
* `file` items will open the file returned by the given request when selected.
  * Unlike `web`, this allows additional request parameters and relative paths.
  * Implementations might show a preview of the file or offer to open it with a specialized app.
  * Might not be implemented by all HyperMenu clients.
* If none of the selectable properties is available, the item can not be selected (*info item*).

```swift
class Request {
  var method: String?
  var url: String
  var headers: [String: String?]?
  var stickyHeaders: [String: String?]?
  var body: JSONValue?
}
```

* Auxiliary data structure to represent HTTP requests.
* `method` defaults to `GET`, or `POST` if `body` exists.
* `headers` is a dictionary of request headers.
  * Header order is not preserved.
  * The behavior when using the same key multiple time is undefined.
* `stickyHeaders` are added to all requests down the hierarchy (e.g. authentication).
  * Headers can be replaced, or removed (using `null`).
  * Replacement priority is: `headers`, `stickyHeaders`, inherited `stickyHeaders`.
  * `stickyHeaders` are ignored if the request protocol, domain, or port don't match (prevent cross site attacks).
* `body` is arbitrary JSON data which is added to the request body.
  * Setting this will set `method` to `POST` and the `Content-Type` header to `application/json`, unless defined otherwise.
  * The root body object should either be of type array or object; other values might have special meanings in the future.

```swift
class Action {
  var request: Request?
  var alert: Alert?
  var replace: Menu?
  var back: Int?
  var invalidate: Int?
}
```

* Represents an action that can be triggered by selecting an action item.
* `request` fetches a new action object.
  * Unless the new action object is *empty* (no valid properties), it will *replace* the current action.
  * The new action can't contain another request (currently ignored).
* `alert` shows an alert.
* `replace` replaces the current menu.
* `back` will pop the current menu and move up the hierarchy.
  * `1` will go back to the parent menu; `2` to it's parent, etc.
  * `0` will pop the whole menu hierarchy (*exit*); `-1` will go the first menu (root), etc.
* `invalidate` signals that parent menus are no longer valid and have to be reloaded.
  * `1` means the parent needs a refresh, `2` the parent and its parent, etc.
  * Used for example when an action deletes an item that was included in the parent menu.
  * Current menu is never invalidated (use `replace`).
  * Menus that haven't been fetched using a request (inline menus) are ignored.
* All navigation properties are evaluated relative to the current menu, even when combined.
  * `{"replace": {}, "back": 2, "invalidate": 1}` would go back two menus. The replaced and invalidated menus are dropped immediately.

```swift
class Alert {
  var title: String?
  var message: String?
  var button: String?
}
```

* Defines an alert to be shown by an action.
* `button` is used as label for the button to dismiss the alert (default: `OK`).
