# DoctrineFacade
Extendable class for an entity facade service

composer.json

    "require": {
    "djaney/DoctrineFacade": "dev-master"
    },
    "repositories": [
    {
        "type": "vcs",
        "url":  "git@github.com:djaney/DoctrineFacade.git"
    }
    ]


services.yml


    services:
        facade.employee:
            class:        Djaney\DoctrineFacade\EntityFacade
            arguments:    [@doctrine,"AppBundle\Entity\Employee"]

        facade.project:
            class:        Djaney\DoctrineFacade\EntityFacade
            arguments:    [@doctrine,"AppBundle\Entity\Project"]
