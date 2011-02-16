PHPoton
=======
A (simple) tweetbot.


TODO
-----------
    [X] Everybody who response to the bot must be followed
    [X] submit textarea must have a "140 chars left counter"
    [X] service to convert twitter id to screen-name (and to be added to the tweeps-table)
    [X] parse answers
    [X] create cronjob to glue everything together
    [X] create decent web interface
    [X] moderation backend (with zend_auth authentication)
    [X] status table has question_id, and questions has a active-enum.. conflicts! (
    [X] implement time limit for answers
    [X] implement scoreboard
    [X] questions should have a paginator as well
    [X] question's should be removed. Statistics should display stats and should be paginated
    [ ] create a 'close now' and 'new question now' button for admin
    [ ] fix the ranking, let people share a place when they have the same score

TODO v2
------------
    [ ] More points (10, 7, 5, 3, 1)
    [ ] Create "groups" with scoring (first answer gets a point in the group)
    [ ] Each question runs fixed amount of time
    [ ] Adding users to groups by group leader
    [ ] make cron only callable from CLI only
    [ ] One answer per user (tweet message back to user if answered multiple times)
    [ ] Blocklist (from who?)
    [ ] Create a API
