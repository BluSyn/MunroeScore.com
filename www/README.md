MunroeScore.com
-----------------

The purpose of this project is to put Munroe's Law to the test.
For those who are unfamiliar with Munrow's Law, checkout xkcd.com/903/

Currently this is just a command-line script. Eventually I intend to make
this a web-accessible app at MunroeScore.com.

The biggest hurdle currently is I don't want to overload wikipedia's
servers with useless requests, thereby getting my server blocked. There are
several alternatives I am considering, such as re-writting the core component in
JavaScript, thereby putting the requests on the client side.

For now, anyone can run this utility from command line, just requires
PHP to be installed on your local machine.

Examples
--------------

```
$ ./getscore.php Thomas_Jefferson

Page: Thomas_Jefferson
Loading: Thomas_Jefferson... Next link found: Founding_Fathers_of_the_United_States
Loading: Founding_Fathers_of_the_United_States... Next link found: American_Revolution
Loading: American_Revolution... Next link found: Thirteen_Colonies
Loading: Thirteen_Colonies... Next link found: English_colonial_empire
Loading: English_colonial_empire... Next link found: Kingdom_of_England
Loading: Kingdom_of_England... Next link found: Sovereign_state
Loading: Sovereign_state... Next link found: State_%28polity%29
Loading: State_%28polity%29... Next link found: Government
Loading: Government... Next link found: Legislators
Loading: Legislators... Next link found: Legislature
Loading: Legislature... Next link found: Deliberative_assembly
Loading: Deliberative_assembly... Next link found: Organization
Loading: Organization... Next link found: Social_group
Loading: Social_group... Next link found: Social_sciences
Loading: Social_sciences... Next link found: Field_of_study
Loading: Field_of_study... Next link found: Knowledge
Loading: Knowledge... Next link found: Information
Loading: Information... Next link found: Order_theory
Loading: Order_theory... Next link found: Mathematics
Loading: Mathematics... Next link found: Quantity
Loading: Quantity... Next link found: Property_%28philosophy%29
Loading: Property_%28philosophy%29... Next link found: Modern_philosophy
Loading: Modern_philosophy... Final link: Philosophy

End point reached.
Munroe Score: 23

27.665448904037 seconds
```
