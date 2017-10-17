# Post Expiration

A WordPress module that makes it easy to add support for post expiration to any post type.

## Requirements

- PHP 5.4+
- WordPress 4.5+

## Installation

Add the module to your code base via [Composer](https://getcomposer.org/)

```SHELL
composer require wpscholar/wp-post-expiration
```

## Initialization

If you are adding the code to a WordPress plugin or theme, there is no initialization step needed. However, if you are adding the code at a higher level in your WordPress project, you will need to call the initialization function on the init hook, like so:

```PHP
add_action( 'init', 'wpscholar_post_expiration_initialize', 1000 );
```

## Adding Post Type Support

If you are adding support to a pre-existing post type, just add this code:

```PHP
add_post_type_support( 'post', 'expiration' );
```

Be sure to replace `post` with the name of your post type.

Or, in the `supports` argument when registering a post type, just add `'expiration'`.

## Available Methods

The following static methods are publicly available:

- `setExpiration( $post_id, $expiration )` - Set expiration for a specific post. Expiration is a Unix timestamp.
- `removeExpiration( $post_id )` - Remove expiration for a specific post.
- `expirePost( $post_id )` - Immediately expire a specific post.
- `expirePosts()` - Expire all posts. (Limit 100 per run per post type)