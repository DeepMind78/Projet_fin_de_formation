<?php

namespace App\EventSubscriber;

use App\Controller\CoachController;
use App\Entity\Coach;
use CalendarBundle\Entity\Event;
use App\Repository\RdvRepository;
use CalendarBundle\CalendarEvents;
use App\Repository\CoachRepository;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{

    private $manager;
    private $repo;
    private $id;
    private $router;

    public function __construct(EntityManagerInterface $manager, RdvRepository $repo, UrlGeneratorInterface $router)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->router = $router;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {   
        
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        

        // You may want to make a custom query from your database to fill the calendar

        // $rdvs = $this->repo->findBy(['coach'=>$this->id]);
        // dump($this->id);
        $rdvs = $this->repo->findAll();
        dump($rdvs);
        foreach($rdvs as $rdv){
            $date = date_format($rdv->getHeure(), 'H:i') . ' Indisponible';
            $calendar->addEvent( $booking = new Event(
                $date,
                $rdv->getJour()
            ));
            $booking->setOptions(['backgroundColor'=>'red', 'borderColor'=>'red']);
            
        }

        // $calendar->addEvent(new Event(
        //     'Event 1',
        //     new \DateTime('Tuesday this week'),
        //     new \DateTime('Wednesdays this week')
        // ));

        
        // $calendar->addEvent(new Event(
        //     'All day event',
        //     new \DateTime('Friday this week')
        // ));

    }
}