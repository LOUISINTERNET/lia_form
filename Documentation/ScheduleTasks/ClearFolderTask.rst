.. _clearFolderTask:

===============
ClearFolderTask
===============

This task delete old file that are uploaded by your forms. You can define how old the
files can get. And all older files will be deleted.

Additional settings
===================

.. confval:: liaHoursToLive
    :name: liaHoursToLive
    :required: false
    :type: integer
    :default: 1

    Define the maximum live time of uploaded files.

.. confval:: liaFolderToClear
    :name: liaFolderToClear
    :required: false
    :type: integer
    :default: 1

    The folder that has to be cleaned.
