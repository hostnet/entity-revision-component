
UPGRADE FROM 1.0 to 1.1
===============================

### General Changes

- Changed when `RevisionFactoryInterface::createRevion()` is called. Previously it 
  called on every preFlush, now only when it's actually required.

- Deprecated `RevisionListener::preFlush()`, please remove this registration from 
  the event manager. The functionality will not break but does create a small overhead.

- Introduced `RevisionListener::postFlush()`. This method will reset the previous
  revision created in `RevisionListener::entityChanged()`. You can overwrite this method
  safely in case you want 1 revision per request instead of flush. Note that you don't
  have access to the actual revision.
