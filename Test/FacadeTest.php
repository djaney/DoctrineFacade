<?php namespace Djaney\DoctrineFacade\Test;

use Djaney\DoctrineFacade\EntityFacade;

class FacadeTest extends \PHPUnit_Framework_TestCase
{
    private $doctrine;
    private $employee;
    public function setUp()
    {
        // First, mock the object to be used in the test
        $employee = $this
            ->getMockBuilder('AppBundle\Entity\Employee')
            ->setMethods(['getName','getId'])
            ->getMock()
        ;

        $employee->expects($this->any())
            ->method('getName')
            ->willReturn('Geraldine');

        $employee->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        // Now, mock the repository so it returns the mock of the employee
        $employeeRepository = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $employeeRepository->expects($this->any())
            ->method('find')
            ->willReturn($employee);

        // mock the EntityManager to return the mock of the repository
        $entityManager = $this
            ->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($employeeRepository);


        // mock doctrine service
        $doctrine = $this
            ->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->any())
            ->method('getManager')
            ->willReturn($entityManager);

        $doctrine->expects($this->any())
            ->method('getRepository')
            ->willReturn($employeeRepository);

        $this->employee = $employee;
        $this->doctrine = $doctrine;
    }

    public function testGetById(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->getById(1);
        $this->assertEquals('Geraldine', $e->getName());
    }

    public function testSetSubject(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->setSubject($this->employee);
        $this->assertEquals($this->employee, $e->getSubject());
    }

    public function testSetSubjectById(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->setSubjectById(1);
        $this->assertEquals(1, $e->getSubject()->getId());
    }

    public function testclean(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->setSubject($this->employee);
        $ef->clean();
        $this->assertEquals(null, $ef->getSubject());
    }

    public function testFlush(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');

        // expect manager flush to be called
        $this->doctrine->getManager()->expects($this->once())
            ->method('flush');

        $ef->flush();
    }

    public function testCreate(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $employee = $ef->create(function($o){
            $o->setName('Geraldine');
        })->getSubject();
        $this->assertInstanceOf('AppBundle\Entity\Employee', $employee);
    }

    public function testMutate(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->setSubjectById(1)
            ->mutate(function($employee){
                $this->assertEquals('Geraldine', $employee->getName());
            });
    }

    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testMutateExceptions(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef
            ->mutate(function($employee){
                $this->assertEquals('Geraldine', $employee->getName());
            });
    }
}
