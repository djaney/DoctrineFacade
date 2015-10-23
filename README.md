[![Build Status](https://travis-ci.org/djaney/DoctrineFacade.svg?branch=master)](https://travis-ci.org/djaney/DoctrineFacade)

# DoctrineFacade
Extendable class for an entity facade service

composer.json

    "require": {
    "djaney/doctrine-facade": "dev-master"
    },


services.yml


    services:
        facade.employee:
            class:        Djaney\DoctrineFacade\EntityFacade
            arguments:    [@doctrine,"AppBundle\Entity\Employee"]

        facade.project:
            class:        Djaney\DoctrineFacade\EntityFacade
            arguments:    [@doctrine,"AppBundle\Entity\Project"]
