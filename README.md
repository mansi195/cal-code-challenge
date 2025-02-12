This project is a Symfony-based application for managing consultant meetings. 

Steps I have followed - 
1. I have modified the existing Employee and Project Planning Entity to include following   relationships -
    - src\Entity\Employee.php: OnetoMany Relationship with ProjectPlanning
    - src\Entity\ProjectPlanning.php: ManyToOne Relationship with Employee

2. To retrieve SQL data in a Symfony controller via an Entity I have used Doctrine ORM, for this  I created repository classes for each entity
    - src\Repository\EmployeeRepository.php
    - src\Repository\ProjectPlanningRepository.php

3. Installed 'spatie/icalendar-generator' library to create .ics files
    - Create: composer require spatie/icalendar-generator

4. Command class
    - Create: php bin/console make:command ExportMeetings
    - Filename: src\Command\ExportMeetingsCommand.php
    - Run command for all employees: php bin/console app:employee-meetings
    - Run command for each employee: php bin/console app:employee-meetings --employee-email="user1@example.org"  --output-dir="output/"
    
5. Employee Controller class
    - Create: php bin/console make:controller Employee
    - Filename: src\Controller\EmployeeController.php

    - Route: '/' listing all employees
    - Template Filename: src\templates\employee\index.html.twig
    - Functionality: able to view meeting per employee

    - Route: '/employee/meetings/{email}' for each employee listing all meetings
    - Template Filename: src\templates\employee\meetings.html.twig
    - Functionality: able to edit meeting entries in Meeting Controller class

5. Meeting Controller class
    - Create: php bin/console make:controller Meeting
    - Filename: src\Controller\MeetingController.php

    - Create Form: php bin/console make:form meeting
    - Filename: src\Form\MeetingType.php

    - Route: '/meeting/edit/{id}' meeting edit form
    - Template Filename: src\templates\meeting\edit.html.twig
    - Functionality: able to update meeting entry details

6. Service 
    - Filename: src\Service\MeetingService.php
    - Functionality: retrieve all meetings with or without email id param