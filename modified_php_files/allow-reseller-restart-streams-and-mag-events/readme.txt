i have added restart button permission for reseller groups, edit the group and allow it. 

default filtering function doesn't have live + down channels only filter, so i had to allow every stream in the server,
but they can only restart live and down streams, they can't restart stopped streams.


it needs a column in member_groups table.

run this mysql query, then copy php files to admin folder.

ALTER TABLE xtream_iptvpro.member_groups ADD COLUMN reseller_controls_streams tinyint(4) NOT NULL DEFAULT '0';


also it has the reseller mag event thing in same files, you can disable it in panel settings>reseller permissions.

