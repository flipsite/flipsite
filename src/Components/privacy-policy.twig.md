We at {{company.name}} take your privacy seriously and are firmly committed to protecting your privacy. This privacy notice describes how we collect, receive, use, store, share, transfer, and process your personal information. In short: We do not sell your data. This privacy policy states how we ensure your privacy in compliance with the European Union’s General Data Protection Regulation (GDPR) as well as the California Consumer Privacy Act (CCPA). If you have any questions on how your data is protected, don't hesitate to contact us by using the contact information at the bottom of this page.

This Policy describes the information we collect from you, how we use that information and our legal basis for doing so. It also covers whether and how that information may be shared and your rights and choices regarding the information you provide to us. This Privacy Policy applies to the information that we obtain through your use of our websites, including its subdomains.

## Register holder

Name: {{company.name}}, {{company.businessId}}
Address: {{company.address}}

## Name of register

{{nameOfRegister}}

## What we collect and receive

In order for us to provide you the best possible experience on our websites, we need to collect and process certain information. Depending on your use of the Services, that may include:

- **Contact us via email or web form** — for example, when you ask for support or quote, send us questions or comments, or report a problem, we will collect your name, email address, message, etc. We use this data solely in connection with answering the queries we receive.
- **Usage data** — when you visit our site, we will store: the website from which you visited us from, the parts of our site you visit, the date and duration of your visit, your IP address, information from the device (device type, operating system, screen resolution, language, country you are located in, and web browser type) you used during your visit, and more. We process this usage data for statistical purposes, to improve our site and to recognize and stop any misuse.
- **Cookies** — we may use cookies (small data files transferred onto computers or devices by sites) for record-keeping purposes and to enhance functionality on our site. You may deactivate or restrict the transmission of cookies by changing the settings of your web browser. Cookies that are already stored may be deleted at any time.
{% if accountCreation %}
- **Create an account** — when you sign up for and open an account or sign up for content or offers, we may ask you to provide us with information such as your name, email address and details about your organization. As otherwise detailed in this Privacy Policy, we will solely process this information to provide you with the service you signed up for.
{% endif %}

## Your Rights

You have the right to be informed of Personal Data processed by {{company.name}}, a right to rectification/correction, erasure and restriction of processing. You also have the right to ask from us a structured, common and machine-readable format of Personal Data you provided to us. We can only identify you via your email address and we can only adhere to your request and provide information if we have Personal Data about you through you having made contact with us directly and/or you using our site and/or service. We cannot provide, rectify or delete any data that we store on behalf of our users or customers. To exercise any of the rights mentioned in this Privacy Policy and/or in the event of questions or comments relating to the use of Personal Data you may contact {{company.name}}'s support team: [{{company.email}}](mailto:{{company.email}}) In addition, you have the right to lodge a complaint with the data protection authority in your jurisdiction.
{% if subprocessors|length %}
## Subprocessors

We use a select number of trusted external service providers for certain technical data processing and/or service offerings. These service providers are carefully selected and meet high data protection and security standards. We only share information with them that is required for the services offered and we contractually bind them to keep any information we share with them as confidential and to process Personal Data only according to our instructions. {{company.name}} uses the following subprocessors to process the data collected by our websites:

| Subprocessor | Data location and security | Service |
| --- | --- | --- |
{% for sub in subprocessors %}
| [{{sub.name}}]({{sub.url}}) | {{sub.location}} | {{sub.service}} |
{% endfor %}
{% endif %}

{% if thirdParty|length %}
## Third party services we use

When you visit our websites, or purchase products or services, we use the following third party services which may collect personal data:

| Recipient | Purpose of processing | Lawful basis | Data location and security | Personal data collected by the third party |
| --- | --- | --- | --- | --- |
{% for tp in thirdParty %}
| [{{tp.name}}]({{tp.url}}) | {{tp.purpose}} | {{tp.basis}} | {{tp.dataLocation}} | {{tp.personalData}} |
{% endfor %}
{% endif %}
## Retention of data

We will retain your information as long as your account is active, as necessary to provide you with the services or as otherwise set forth in this Policy. We will also retain and use this information as necessary for the purposes set out in this Policy and to the extent necessary to comply with our legal obligations, resolve disputes, enforce our agreements and protect {{company.name|s}} legal rights. We also collect and maintain aggregated, anonymized or pseudonymized information which we may retain indefinitely to protect the safety and security of our Site, improve our Services or comply with legal obligations.

## Principles for securing data files/registers

Only those persons who need the data for maintenance and service are allowed to access data. Data is secured with user ids and passwords for them. The main users can use their main user role only when they fulfill maintenance, error detection or when they take care of customers’ tasks concerning their data. The data is secured with passwords and other technical means. Data is transferred over a secured HTTPS connection.

We use and maintain firewalls and corresponding security solution in all devices that can be connected directly to public networks.

## Privacy policies of other websites

Our website contains links to other websites. Our privacy policy applies only to our website, so if you click on a link to another website, you should read their privacy policy.

## Privacy Policy Changes

We may update this Policy from time to time. If we do, we’ll let you know about any material changes, either by notifying you on the website or by sending you an email.

## Contact Us

If you have any questions or concerns regarding your information, please contact the Data Protection Officer below. Please note that it may take up to 30 days to get back to you.

Data Protection Officer: **{{dpo.name}}**, [{{dpo.email}}](mailto:{{dpo.email}}), [{{dpo.phone|tel}}](tel:{{dpo.phone}})
