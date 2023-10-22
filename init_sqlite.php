<?php
$db = new SQLite3($dbf);
$db->exec("

CREATE TABLE IF NOT EXISTS projects (
    project_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    project_name TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS tasks (
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

create table if not exists product(
    p_id INTEGER PRIMARY KEY AUTOINCREMENT,
    product TEXT NOT NULL UNIQUE on conflict ignore,
    count INTEGER DEFAULT 1
);

create table if not exists items(
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

create table if not exists storage(
    str_id INTEGER PRIMARY KEY AUTOINCREMENT,
    str_name TEXT NOT NULL UNIQUE on conflict ignore
);

create table if not exists zone(
    zone_id INTEGER PRIMARY KEY AUTOINCREMENT,
    zone_name TEXT NOT NULL,
    storage_id INTEGER,
    UNIQUE(zone_name,storage_id) on conflict ignore 
);

create table if not exists links(
    link_id INTEGER PRIMARY KEY AUTOINCREMENT,
item INTEGER,
storage INTEGER,
zone INTEGER,
count not null default 1,
expire datetime,
detail text,
UNIQUE(item,storage,zone) on conflict ignore 
);

create table if not exists tags(
    tag_id INTEGER PRIMARY KEY AUTOINCREMENT,
    tag_name TEXT NOT NULL UNIQUE on conflict ignore
);

");
$db->close();
?>
