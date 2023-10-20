CREATE TABLE projects (
    project_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    project_name TEXT    NOT NULL
);
CREATE TABLE tasks (
    task_id        INTEGER PRIMARY KEY AUTOINCREMENT,
    task_name      TEXT    NOT NULL,
    completed      INTEGER NOT NULL,
    start_date     TEXT,
    completed_date TEXT,
    project_id     INTEGER NOT NULL,
    FOREIGN KEY (
        project_id
)
REFERENCES projects (project_id) ON UPDATE CASCADE
ON DELETE CASCADE
);
CREATE TABLE product(
    p_id INTEGER PRIMARY KEY AUTOINCREMENT,
    product TEXT NOT NULL UNIQUE on conflict ignore,
    count INTEGER DEFAULT 1
);
CREATE TABLE items(
    item_id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_name INTEGER,
    cost INTEGER DEFAULT 0,
    weight INTEGER DEFAULT 0,
    expire INTEGER DEFAULT 0,
    inspect INTEGER DEFAULT 7,
    date datetime default current_timestamp,
    source TEXT,
    detail TEXT, 
    storage INTEGER,
    count INTEGER DEFAULT 1
);
CREATE TABLE storage(
    str_id INTEGER PRIMARY KEY AUTOINCREMENT,
    str_name TEXT NOT NULL UNIQUE on conflict ignore
);
CREATE TABLE zone(
    zone_id INTEGER PRIMARY KEY AUTOINCREMENT,
    zone_name TEXT NOT NULL,
    storage_id INTEGER,
    UNIQUE(zone_name,storage_id) on conflict replace
);
CREATE TABLE links(
    link_id INTEGER PRIMARY KEY AUTOINCREMENT,
item INTEGER,
storage INTEGER,
zone INTEGER,
expire datetime,
detail text, count not null default 0,
UNIQUE(item,storage,zone) on conflict replace
);
CREATE TABLE tags(
    tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
    tag_name TEXT NOT NULL UNIQUE on conflict ignore
);
/* No STAT tables available */
