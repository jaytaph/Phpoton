PHPoton
=======
A (simple) tweetbot.


TODO
-----------

    [ ] twitter's status_id is treated as a sequential number when ordering replies. We should not rely on this fact.
    [ ] submit textarea must have a "140 chars left counter"
    [X] service to convert twitter id to screen-name (and to be added to the tweeps-table)
    [X] parse answers
    [ ] create cronjob to glue everything together
    [ ] create decent web interface
    [ ] moderation backend (with zend_auth authentication)
    [ ] status table has question_id, and questions has a active-enum.. conflicts!

TODO v2
------------
[ ] Create "groups" with scores
[ ] Adding users to groups by group leader
