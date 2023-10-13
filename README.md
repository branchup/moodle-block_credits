Block credits
=============

Supports the management of user credits.

Requirements
------------

- Moodle 4.1

Installation
------------

- Install the block
- Add the block to the front page, dashboard, or a course
- Assign the `block/credits:manage` permission
- Review the `block/credits:viewall` permission
- Review the `block/credits:view` permission if used outside a course

Public API
----------

You can give credits to individual users by calling the following API method:

**block_credits_credit_user_for_purchase**

Its parameters are:

- userid `int`: The Moodle user ID
- quantity `int`: The number of credits to give
- validuntil `int`: The unix timestamp up until the credits are valid
- reference `string` (optional): An optional reference visible to the end-user in their transactions.

Notes
-----

- Credits are not context-dependent, they are recorded site-wide.
- To manage credits, a user must have the permission to in a course, or the system.
- In the context of a course, the recipient of the credits must be enrolled in the course.
- Add the block in a system page to manage recipients more globally.

