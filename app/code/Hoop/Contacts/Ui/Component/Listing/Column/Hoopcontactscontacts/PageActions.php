<?php
namespace Hoop\Contacts\Ui\Component\Listing\Column\Hoopcontactscontacts;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["hoop_contacts_contact_id"]))
                {
                    $id = $item["hoop_contacts_contact_id"];
                }
                $item[$name]["view"] = [
                    "href"=>$this->getContext()->getUrl(
                        "hoop_contacts_contacts/contact/edit",["hoop_contacts_contact_id"=>$id]),
                    "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
