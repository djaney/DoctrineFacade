<?php
namespace Djaney\DoctrineFacade;
class EntityFacade {

    protected $doctrine;
    protected $subject = null;
    protected $em = null;
    protected $className = null;

    public function __construct($doctrine,$className = null){
        $this->doctrine = $doctrine;
        $this->className = $className;
        $this->em = $this->doctrine->getManager();
    }

    public function getCollection($limit = 10, $orderBy = null, $direction = 'ASC'){
        // TODO
    }

    public function getById($id){
        return $this->doctrine->getRepository($this->className)
        ->find($id);
    }
    public function setSubject($subject){
        $this->subject = $subject;
        return $this;
    }
    public function setSubjectById($id){
        return $this->setSubject($this->getById($id));
    }
    public function getSubject(){
        return $this->subject;
    }
    public function clean(){
        $this->subject = null;
        return $this;
    }
    public function flush(){
        $this->em->flush();
        return $this->clean();
    }

    public function create(){

        $r = new \ReflectionClass($this->className);
        $subject = $r->newInstanceArgs();
        $this->em->persist($subject);
        $this->em->flush();
        $this->setSubject($subject);
        return $this;
    }

    public function mutate(\Closure $callback){
        if($this->subject===null) throw new InvalidFacadeSubjectException();
        $callback($this->subject);
        return $this;
    }
    public function patch($array){
        if($this->subject===null) throw new InvalidFacadeSubjectException();
        $r = new \ReflectionClass($this->subject);
        foreach($array as $k=>$v){
            if( $k=='id' ) continue;
            $method = 'set' . ucfirst($k) ;
            if( $r->hasMethod( $method ) ){
                $this->subject->$method($v);
            }
        }
        return $this;
    }

    public function delete(){
        if($this->subject===null) throw new InvalidFacadeSubjectException();
        $this->em->remove($this->subject);
        return $this;
    }

}
