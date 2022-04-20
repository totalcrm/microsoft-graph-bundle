<?php

namespace TotalCRM\MicrosoftGraph\Manager;

use RuntimeException;
use TotalCRM\MicrosoftGraph\DependencyInjection\MicrosoftGraphRequest;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Cursor;

use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Http\GraphCollectionRequest;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Model;
use Exception;

/**
 * Class ContactManager
 * @package TotalCRM\MicrosoftGraph\Manager
 */
class ContactManager
{
    private MicrosoftGraphRequest $request;
    private ?OutputInterface $output = null;

    /**
     * ContactManager constructor.
     * @param MicrosoftGraphRequest $request
     */
    public function __construct(MicrosoftGraphRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutputInterface(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @param string $contactFolder
     * @param bool|null $isContactAll
     * @param GraphCollectionRequest|null $collectionRequest
     * @return mixed
     * @throws Exception
     */
    public function getContacts($contactFolder = null, $isContactAll = true, GraphCollectionRequest $collectionRequest = null)
    {
        if ($contactFolder !== null) {
            $endpoint = '/me/contactfolders/'.$contactFolder.'/contacts';
        } else {
            $endpoint = '/me/contacts'; 
        }

        if (!$collectionRequest) {
            /** @var GraphCollectionRequest $collectionRequest */
            $collectionRequest = $this->request
                ->createCollectionRequest("GET", $endpoint)
                ->setReturnType(Model\Contact::class)
            ;
            $collectionRequest->setPageSize(500);
        }
        
        $results = [];
        $iteration = 1;
        $totalItems = 0;

        if ($this->output instanceof OutputInterface) {
            $dt = new \DateTime();
            $cursor = new Cursor($this->output);
            $this->output->writeln(['<info>' . $dt->format('Y-m-d H:i:s') . ' - Import contacts</info>']);
        }

        execute:
        $resultsExecute = $collectionRequest->getPage();

        $totalItems = $totalItems + count($resultsExecute);
        if ($this->output instanceof OutputInterface) {
            $dt = new \DateTime();
            $cursor->moveUp();
            $cursor->clearLine();
            $this->output->writeln(['<info>' . $dt->format('Y-m-d H:i:s') . ' - Import contacts: '.$totalItems.'</info>']);
        }

        foreach ($resultsExecute as $item) {
            $results[] = $item;
        }

        if ($iteration <= 1000 && !$collectionRequest->isEnd() && $isContactAll) {
            ++$iteration;
            goto execute;
        }

        if ($this->output instanceof OutputInterface) {
            $dt = new \DateTime();
            $cursor->moveUp();
            $cursor->clearLine();
            $this->output->writeln(['<info>' . $dt->format('Y-m-d H:i:s') . ' - Total imported: '.$totalItems.'</info>']);
        }

        if (!$isContactAll) {
            return [
                'results' => $results,
                'collectionRequest' => $collectionRequest,
                'isEnd' => $collectionRequest->isEnd()
            ];
        }

        return $results;
    }

    /**
     * @return Model\Contact|array
     * @throws Exception
     */
    public function getContactFolders(): array
    {
        $endpoint = '/me/contactFolders';

        return $this->request
            ->createCollectionRequest("GET", $endpoint)
            ->setReturnType(Model\Contact::class)
            ->execute();
    }

    /**
     * @return Model\Contact|null
     * @throws Exception
     */
    public function getContactFolder($contactFolder = null)
    {
        $endpoint = '/me/contactFolders/' . $contactFolder;

        return $this->request
            ->createCollectionRequest("GET", $endpoint)
            ->setReturnType(Model\Contact::class)
            ->execute();
    }

    /**
     * @param string|null $email
     * @return Model\Contact|mixed
     * @throws Exception
     */
    public function findContactsByEmail($email = null, $contactFolder = null)
    {
        if ($email === null) {
            throw new RuntimeException("Your email is null");
        }

        if ($contactFolder !== null) {
            $endpoint = '/me/contactfolders/' . $contactFolder.'/contacts';
        } else {
            $endpoint = '/me/contacts';
        }

        return $this->request
            ->createRequest('GET', $endpoint . '?$filter=emailAddresses/any(a:a/address eq ' . urlencode("'" . $email . "'") . ')&$count=true')
            ->setReturnType(Model\Contact::class)
            ->execute();
    }

    /**
     * @param string|null $email
     * @return Model\Contact|mixed
     * @throws Exception
     */
    public function findContactsByPhone($phone = null, $contactFolder = null)
    {
        if ($phone === null) {
            throw new RuntimeException("Your phone is null");
        }

        if ($contactFolder !== null) {
            $endpoint = '/me/contactfolders/'.$contactFolder.'/contacts';
        } else {
            $endpoint = '/me/contacts';
        }
        $request = $this->request
            ->createRequest('GET', $endpoint . '?$filter=mobilePhone eq ' . urlencode("'". $phone . "'") .'&$count=true')
            ->addHeaders(["ConsistencyLevel" => "eventual"])
            ->setReturnType(Model\Contact::class)
        ;

        return $request->execute();
    }

    /**
     * @param $contactId
     * @return Model\Contact|mixed
     * @throws Exception
     */
    public function getContact($contactId = null)
    {
        if ($contactId === null) {
            throw new RuntimeException("Your contactId is null");
        }

        $request = $this->request
            ->createRequest('GET', '/me/contacts/' . $contactId)
            ->setReturnType(Model\Contact::class)
        ;
        
        return $request->execute();
    }

    /**
     * Create an contact
     * @param Model\Contact $contact
     * @return mixed|array|void
     * @throws Exception
     */
    public function addContact(Model\Contact $contact = null)
    {
        if ($contact === null) {
            throw new RuntimeException("Your Contact is null");
        }

        return $this->request
            ->createRequest('POST', '/me/contacts')
            ->attachBody($contact->jsonSerialize())
            ->setReturnType(Model\Contact::class)
            ->execute();
    }

    /**
     * Update an Contact
     * @param Model\Contact|null $contact
     * @return mixed|array|void
     * @throws Exception
     */
    public function updateContact(?Model\Contact $contact = null)
    {
        if ($contact === null) {
            throw new RuntimeException("Your contact is null");
        }

        return $this->request
            ->createRequest('PATCH', '/me/contacts/' . $contact->getId())
            ->attachBody($contact->jsonSerialize())
            ->setReturnType(Model\Contact::class)
            ->execute();
    }

    /**
     * Delete an contact
     * @param $id
     * @return mixed|array
     * @throws Exception
     */
    public function deleteContact($id = null)
    {
        if ($id === null) {
            throw new RuntimeException("Contact id is null");
        }

        return $this->request
            ->createRequest('DELETE', '/me/contacts/' . $id)
            ->execute();
    }

}