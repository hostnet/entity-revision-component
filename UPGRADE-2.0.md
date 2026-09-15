UPGRADE FROM 1.x to 2.0
===============================

### General Changes

- Dropped support for `doctrine/annotations`. Entities must use the `#[Revision]`
  attribute instead of the `@Revision` annotation.

- Removed `RevisionListener::preFlush()`. This method has been deprecated since 1.1
  and its functionality already lives in `RevisionListener::entityChanged()` and
  `RevisionListener::postFlush()`. Remove any `preFlush` registration of the
  `RevisionListener` from your event manager, if you still have one.

- Added return type hints to `RevisionableInterface`, `RevisionInterface` and
  `RevisionFactoryInterface`. Any implementation of these interfaces must add
  matching return types:
  - `RevisionableInterface::setRevision()`: `static` (now fluent, must `return $this;`)
  - `RevisionableInterface::getRevision()`: `?RevisionInterface`
  - `RevisionInterface::getUser()`: `?string`
  - `RevisionInterface::getCreatedAt()`: `\DateTimeInterface`
  - `RevisionFactoryInterface::createRevision()`: `RevisionInterface` (no longer
    nullable)
