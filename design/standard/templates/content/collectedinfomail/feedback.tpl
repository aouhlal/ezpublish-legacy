{set-block scope=root variable=subject}{"Feedback from %1"|i18n("design/standard/content/edit",,array($collection.object.name))}{/set-block}

{* Use this line to specify the e-mail in the template, can read this from the object to
   make it dynamic pr form
{set-block scope=root variable=email_receiver}nospam@ez.no{/set-block}
*}

{* Set this to redirect to another node
{set-block scope=root variable=redirect_to_node_id}2{/set-block}
*}

{"The following feedback was collected:"|i18n("design/standard/content/edit")}

{section name=Attribute loop=$collection.attributes}
{$Attribute:item.contentclass_attribute_name|wash}:
{attribute_result_gui view=info attribute=$Attribute:item}


{/section}
