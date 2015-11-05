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
            ->setMethods(['getName','getId','setName'])
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
        $employeeRepository->expects($this->any())
            ->method('findBy')
            ->willReturn([
                $employee,
                $employee,
                $employee
            ]);

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

    public function testGet(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $e = $ef->get(1);
        $this->assertEquals('Geraldine', $e->getName());
    }

    public function testPost(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $employee = $ef->post(['name'=>'Geraldine']);
        $this->assertInstanceOf('AppBundle\Entity\Employee', $employee);
    }

    public function testMutate(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->mutate(1,function($employee){
                $this->assertEquals('Geraldine', $employee->getName());
            });
    }

    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testMutateInvalidSubjectException(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->mutate(-1,function($employee){
                $this->assertEquals('Geraldine', $employee->getName());
            });
    }

    public function testPatch(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $this->employee->expects($this->once())
            ->method('setName');
        $ef->patch(1,['name'=>'Djane Rey']);
    }
    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testPatchInvalidSubjectException(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $ef->patch(-1,['name'=>'Djane Rey']);
    }

    public function testDelete(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');

        // expect manager flush to be called
        $this->doctrine->getManager()->expects($this->once())
            ->method('remove');

            $ef->delete(1);
    }
    /**
     * @expectedException Djaney\DoctrineFacade\InvalidFacadeSubjectException
     */
    public function testDeleteInvalidSubjectException(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');

        $ef->delete(-1);
    }

    public function testCollection(){
        $ef = new EntityFacade($this->doctrine,'AppBundle\Entity\Employee');
        $col = $ef->query();
        $this->assertCount(3, $col);
        $this->assertInstanceOf('AppBundle\Entity\Employee', $col[0]);
    }
}
