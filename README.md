README
======
 - [What is the Revision Component?](#what-is-the-entity-revision-component)
 - [Requirements](#requirements)
 - [Installation](#installation)

### Documentation
   - [How does it work?](#how-does-it-work)
   - [Setup](#setup)
     - [Registering the Events](#registering-the-events)
     - [Creating a Revision Entity](#creating-a-revision-entity)
     - [Configuring the Entity](#configuring-the-entity)
     - [Creating the AcmeRevisionFactory](#creating-the-acmerevisionfactory)
     - [What's Next?](#whats-next)


What is the Entity Revision Component?
--------------------------------------
The Entity Revision Component is a library that utilizes the [Entity Tracker Component](https://github.com/hostnet/entity-tracker-component/) and lets you hook in to the entityChanged event.

This component lets you automatically store revisions for a set of entities per flush. 

Requirements
------------
The revision component requires a minimum of php 8.3 and runs on Doctrine2. For specific requirements, please check [composer.json](../master/composer.json).

Installation
------------

Installing is pretty easy, this package is available on [packagist](https://packagist.org/packages/hostnet/entity-revision-component). You can register the package locked to a major as we follow [Semantic Versioning 2.0.0](http://semver.org/).

#### Example

```javascript
    "require" : {
        "hostnet/entity-revision-component" : "2.*"
    }

```
> Note: You can use dev-master if you want the latest changes, but this is not recommended for production code!


Documentation
=============

How does it work?
-----------------

It works by putting the `#[Revision]` attribute and RevisionableInterface on your Entity and registering the listener on the entityChanged event, assuming you have already configured the [Entity Tracker Component](https://github.com/hostnet/entity-tracker-component/#setup).

For a usage example, follow the setup below.

> Note: this component works very well with the [Entity Mutation Component](https://github.com/hostnet/entity-mutation-component). This combination is ideal to go back in time based on revisions.

Setup
-----
 - You have to add `#[Revision]` to your entity
 - You have to add the RevisionableInterface to your entity
 - You need a Revision Entity


#### Registering the events

Here's an example of a very basic setup. Setting this up will be a lot easier if you use a framework that has a Dependency Injection Container.

It might look a bit complicated to set up, but it's pretty much setting up the tracker component for the most part. If you use it in a framework, it's recommended to create a framework specific configuration package for this to automate this away.

> Note: If you use Symfony2, you can take a look at the [hostnet/entity-tracker-bundle](https://github.com/hostnet/entity-tracker-bundle). This bundle is designed to configure the services for you.


```php
use Acme\Bundle\AcmeBundle\AcmeRevisionFactory;
use Hostnet\Component\EntityRevision\Resolver\RevisionResolver;
use Hostnet\Component\EntityTracker\Listener\EntityChangedListener;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;
use Hostnet\Component\EntityTracker\Provider\EntityMutationMetadataProvider;

/* @var $em \Doctrine\ORM\EntityManager */
$event_manager = $em->getEventManager();

// setup required providers
$mutation_metadata_provider = new EntityMutationMetadataProvider();
$metadata_provider          = new EntityMetadataProvider();
$logger = ...; // instance of LoggerInterface from the psr/log package, optional argument

// pre flush event listener that uses the #[Tracked]-derived attributes, such as #[Revision]
$entity_changed_listener = new EntityChangedListener(
    $metadata_provider,
    $mutation_metadata_provider,
    $logger
);

// the resolver is used to find the correct attribute
$revision_resolver = new RevisionResolver($metadata_provider);

// this factory will provide the revision and as author Henk
$revision_factory = new AcmeRevisionFactory('Henk');

// creating the revision listener
$revision_listener = new RevisionListener($revision_resolver, $revision_factory);

$event_manager->addEventListener('preFlush', $entity_changed_listener);
$event_manager->addEventListener('postFlush', $revision_listener);
$event_manager->addEventListener('entityChanged', $revision_listener);

```

> Note: The RevisionListener must be registered on both the onFlush and entityChanged. Because the revisions are grouped per flush, you must also make sure that the RevisionListener is registered *before* the EntityChangedListener. If this is not done, you will recieve unexpected results.

#### Creating a Revision Entity
Revisions are stored in the database, thus need an Entity. You're free to decide how to store it but you should implement the RevisionInterface on it.

```php

use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityRevision\RevisionInterface;

#[ORM\Entity]
class Revision implements RevisionInterface
{
    #[ORM\...]
    private $author;

    #[ORM\...]
    private $created_at;

    public function __construct($author, \DateTime $created_at)
    {
        $this->author     = $author;
        $this->created_at = $created_at;
    }
    
    public function getUser()
    { 
        return $this->author;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }
}

```


#### Configuring the Entity
All we have to do now is put the `#[Revision]` attribute and RevisionableInterface on our Entity.

```php

use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityRevision\Attributes\Revision as RevisionAttribute;
use Hostnet\Component\EntityRevision\RevisionableInterface;
use Hostnet\Component\EntityRevision\RevisionInterface;

#[ORM\Entity]
#[RevisionAttribute]
class MyEntity implements RevisionableInterface
{
    /**
     * The current revision of the contact person
     */
    #[ORM\ManyToOne(targetEntity: Revision::class)]
    #[ORM\JoinColumn(name: 'revision_id', referencedColumnName: 'id')]
    private ?RevisionInterface $revision = null;

    public function setRevision(RevisionInterface $revision): static
    {
        $this->revision = $revision;

        return $this;
    }

    public function getRevision(): RevisionInterface
    {
        return $this->revision;
    }
}

```

#### Creating the AcmeRevisionFactory
The factory is responsible for providing a revision Entity. You're free to fill in what you want and not obligated to using a constructor like in this example. The only requirement is that it creates a Revision.

```php

namespace Acme\Bundle\AcmeBundle;

use Hostnet\Component\EntityRevision\Factory\RevisionFactoryInterface;

class AcmeRevisionFactory implements RevisionFactoryInterface
{
    private $author;
    
    public function __construct($author)
    {
        $this->author = $author;
    }

    public function createRevision(\DateTime $created_at)
    {
        return new Revision($this->author, $created_at);
    }
}

```

#### What's next?
All you have to do now is change multiple entities with `#[Revision]` and they will be grouped by a Revision Entity.
```php
// imagine all instances to be Revisionable
$user->setName('Henk');
$address->setNumber('42');
$em->flush();
var_dump($user->getRevision() === $address->getRevision()); // true

```
