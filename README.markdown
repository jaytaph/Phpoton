PHPoton
=======
A (simple) tweetbot.


TODO
-----------
    [X] Everybody who response to the bot must be followed
    [X] submit textarea must have a "140 chars left counter"
    [X] service to convert twitter id to screen-name (and to be added to the tweeps-table)
    [X] parse answers
    [ ] create cronjob to glue everything together
    [i] create decent web interface
    [X] moderation backend (with zend_auth authentication)
    [X] status table has question_id, and questions has a active-enum.. conflicts! (
    [X] implement time limit for answers
    [X] implement scoreboard
    [X] questions should have a paginator as well
    [X] question's should be removed. Statistics should display stats and should be paginated

TODO v2
------------
    [ ] twitter's status_id is treated as a sequential number when ordering replies. We should not rely on this fact.
    [ ] Create "groups" with scores
    [ ] Adding users to groups by group leader
