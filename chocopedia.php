<?php
$pathPrefix = "";
$isHome = false;
$pageTitle = "Chocopedia — A–Z Chocolate Dictionary | RT Chocos";
$pageDescription = "A practical reference to cacao, chocolate-making, ingredients, flavour and chocolate science — explained in simple language from A to Z.";
$schemaType = "CollectionPage";
$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Chocopedia', 'item' => 'https://www.rtchocos.com/chocopedia']
];

// Chocopedia A–Z Master Glossary Data
$glossary = [
  'A' => [
    [
      'term' => 'Acidity in cocoa',
      'tag' => 'FLAVOUR',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'Acidity describes sharp or sour notes in cacao or cocoa. It is influenced by fermentation, drying, roasting and the cacao itself; it is not the same as bitterness.',
      'matter' => 'It helps explain why two cocoas can taste sharp or mellow even when their colour looks similar.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Alkalization (Dutching)',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Cocoa is treated with an alkaline solution to change its acidity, colour and flavour. It often becomes darker and less sharp, but the result depends on the cocoa and the treatment.',
      'matter' => 'It affects the colour, flavour and recipe performance of cocoa powder, so one type cannot always replace another.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Aroma',
      'tag' => 'SENSORY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'Aroma is the smell of chocolate, noticed before and while eating it. Fermentation, roasting, cacao origin and storage can create fruity, floral, nutty, spicy or roasted aromas.',
      'matter' => 'Aroma is a major part of flavour perception and helps describe what makes one chocolate different from another.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Astringency',
      'tag' => 'SENSORY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'A dry, puckering feeling in the mouth, similar to strong tea. In chocolate it can be influenced by cacao polyphenols, fermentation, roasting and the balance of the recipe.',
      'matter' => 'Recognising it helps distinguish a drying mouthfeel from bitterness and identify possible recipe or bean issues.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Arriba Nacional',
      'tag' => 'CACAO ORIGIN',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'A fine-flavour cacao type associated with Ecuador and known for distinctive aromas. The name alone does not guarantee flavour quality; growing conditions and post-harvest handling also matter.',
      'matter' => 'It is useful when discussing Ecuadorian cacao, but claims about a specific batch should be supported by sourcing information.',
      'extraHtml' => ''
    ],
  ],
  'B' => [
    [
      'term' => 'Bean-to-bar chocolate',
      'tag' => 'CRAFT',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Chocolate made by a producer who starts with cacao beans and manages the key steps through to finished chocolate. It describes a production approach, not an automatic guarantee of quality or ethical sourcing.',
      'matter' => 'It tells readers how the chocolate was made, while leaving quality to be judged by ingredients, process and result.',
      'extraHtml' => '<a href="index.php#chocolate-table" style="display:inline-flex; align-items:center; gap:6px; color:#C6A24C; font-size:12px; font-weight:600; margin-top:8px; text-decoration:none;">Explore Bean-to-Bar on Chocolate Table &rarr;</a>'
    ],
    [
      'term' => 'Bloom',
      'tag' => 'QUALITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A pale, cloudy or whitish layer that can appear on chocolate. Fat bloom comes from changes in fats; sugar bloom forms when moisture dissolves sugar and it later recrystallises on the surface.',
      'matter' => 'Knowing which type of bloom is present helps identify likely storage or processing causes instead of assuming the chocolate is spoiled.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Bonbon',
      'tag' => 'PRODUCT',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A small confection often made with a chocolate shell and a filling such as ganache, caramel, praline or fruit. Moist fillings need careful formulation and shelf-life assessment.',
      'matter' => 'The filling often determines texture and shelf life, so bonbons need more careful formulation than plain bars.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Body',
      'tag' => 'SENSORY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'Body describes how light, heavy, thin or full chocolate feels in the mouth. Fat content, particle size, recipe and melting behaviour all influence this sensation.',
      'matter' => 'It helps tasters describe mouthfeel more precisely than simply calling a chocolate thick or rich.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Broma process',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'A method of pressing cocoa butter from cocoa mass, leaving a solid cocoa cake that is ground into cocoa powder. The amount of fat removed affects the powder\'s composition and behaviour.',
      'matter' => 'It explains how cocoa butter and cocoa powder are separated from the same starting material.',
      'extraHtml' => ''
    ],
  ],
  'C' => [
    [
      'term' => 'Cacao',
      'tag' => 'CACAO',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'The tree and its seeds, which are processed to make cocoa and chocolate. The word is also commonly used for the raw material before or during processing.',
      'matter' => 'Understanding the raw material helps distinguish the growing and post-harvest stages from later chocolate manufacture.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cacao pod',
      'tag' => 'CACAO',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'The fruit of the cacao tree, containing seeds surrounded by sweet pulp. The seeds become cacao beans after fermentation and drying; the pulp and pod are also being explored for other uses.',
      'matter' => 'The pod is the starting point of the cacao supply chain and contains the beans used for chocolate.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cacao varieties',
      'tag' => 'CACAO ORIGIN',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'Criollo, Forastero and Trinitario are familiar broad cacao group names, although real-world genetics are more complex. Variety can influence flavour potential, but fermentation, drying and roasting are also important.',
      'matter' => 'Variety is one influence on flavour potential, but should not be treated as a guarantee of taste or quality.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cacao nibs',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Small pieces of cacao bean left after the shell is removed and the bean is broken. Nibs have an intense cacao flavour and can be eaten, ground into cacao mass or added to chocolate.',
      'matter' => 'Nibs are a useful link between the bean and chocolate mass and can also be used directly in products.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cacao mass / cocoa liquor',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A thick paste made by grinding cacao nibs. It contains both cocoa solids and cocoa butter; despite the word “liquor,” it contains no alcohol.',
      'matter' => 'It is a foundational ingredient in many chocolate recipes because it supplies both cacao solids and cocoa butter.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cocoa butter',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'The natural fat found in cacao beans. Its melting behaviour and ability to form different crystal structures give chocolate much of its shine, snap, setting behaviour and smooth melt.',
      'matter' => 'It is central to tempering, setting, shine, snap and the way chocolate melts in the mouth.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cocoa percentage',
      'tag' => 'LABEL',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'The percentage usually represents the share of cacao-derived ingredients in a chocolate, according to the relevant calculation and rules. It does not by itself tell you sweetness, quality or flavour.',
      'matter' => 'It can help compare formulations, but it should not be used alone to judge bitterness, sweetness or quality.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cocoa powder',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Powder made by pressing some cocoa butter out of cocoa mass and grinding the remaining cocoa cake. Natural and alkalized powders differ in acidity, colour, flavour and performance.',
      'matter' => 'Different powders can change the colour, flavour, acidity and behaviour of a recipe.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Cocoa solids',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'The non-fat components of cacao that contribute much of cocoa\'s colour and flavour. Cocoa solids are different from cocoa butter, which is the fat portion.',
      'matter' => 'The distinction matters because cocoa solids and cocoa butter contribute different properties to chocolate.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Compound coating',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A chocolate-style coating in which some or all cocoa butter is replaced by another permitted fat, depending on the formulation and local rules. It behaves differently from cocoa-butter chocolate.',
      'matter' => 'Its fat system affects handling, setting and labelling, so it should not automatically be treated as the same as chocolate.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Conching',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'A stage where chocolate is mixed and worked, often with heat and airflow. It helps develop flavour, reduce unwanted volatile acidity and improve texture and flow.',
      'matter' => 'It helps determine the final texture and flavour, and the best conditions depend on the recipe and equipment.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Couverture chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Chocolate formulated with relatively high cocoa butter content for fluidity in coating, moulding and enrobing. It generally needs correct tempering for shine and a clean snap.',
      'matter' => 'Its flow makes it useful for coating and moulding, where an even layer and good finish matter.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Crystallisation',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'The process in which molecules arrange into an ordered solid structure. Cocoa butter forms different crystal types, and the crystal structure affects shine, snap, melting and bloom.',
      'matter' => 'Controlling crystal formation is the foundation of tempering and a major factor in finished chocolate quality.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Criollo',
      'tag' => 'CACAO ORIGIN',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'A cacao group historically associated with fine flavour and delicate beans, though it is less common and can be harder to grow. The name alone does not guarantee quality.',
      'matter' => 'It is a useful cacao-origin term, but the name should not replace evaluation of the actual beans.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Creaminess',
      'tag' => 'SENSORY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'The smooth, rich sensation chocolate creates as it melts. It depends on fat, particle size, milk ingredients where used, and how the formulation melts and coats the mouth.',
      'matter' => 'It helps describe the melt and mouth-coating sensation that can make chocolate feel smooth and rich.',
      'extraHtml' => ''
    ],
  ],
  'D' => [
    [
      'term' => 'Dark chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Chocolate made mainly from cacao-derived ingredients and sugar, usually without milk ingredients. Its flavour can range from fruity and floral to roasted and bitter, depending on cacao and recipe.',
      'matter' => 'The term covers a wide range of recipes and flavours, so cacao percentage alone does not fully describe it.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Deodorised cocoa butter',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Cocoa butter processed to remove much of its natural aroma and flavour. It is useful when a more neutral flavour is wanted, while retaining cocoa butter\'s characteristic fat behaviour.',
      'matter' => 'A more neutral fat flavour can be useful when the recipe should not have a strong cocoa-butter aroma.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Drying of cacao',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'After fermentation, cacao beans are dried to reduce moisture and make them safer to store and transport. Uneven or poorly managed drying can affect flavour and storage quality.',
      'matter' => 'Drying reduces moisture for storage and influences how well beans keep and how their flavour develops.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Dutch-process cocoa',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Another name for alkalized cocoa powder. Treatment changes acidity and often darkens the colour, but flavour and performance depend on the degree of alkalization.',
      'matter' => 'Its altered acidity can affect both flavour and how cocoa behaves in some recipes.',
      'extraHtml' => ''
    ],
  ],
  'E' => [
    [
      'term' => 'Emulsifier',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'An ingredient that helps control how components interact in a mixture. In chocolate, lecithin can influence flow and processing; chocolate is mainly a fat-based suspension rather than a typical water-and-oil emulsion.',
      'matter' => 'Small changes can affect flow and processing, which matters when chocolate must pass through equipment or fill moulds.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Enrobing',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Covering a centre, such as a nut, biscuit or filling, with a layer of chocolate. Centre moisture and temperature, chocolate fluidity and coating thickness all affect the result.',
      'matter' => 'A consistent coating depends on the chocolate\'s flow and on the centre\'s temperature and moisture.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Enzyme',
      'tag' => 'CACAO SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A natural substance that speeds up a chemical reaction. Enzymes and microorganisms help drive changes during cacao fermentation, creating flavour precursors that develop further during roasting.',
      'matter' => 'Enzyme activity contributes to the changes that make fermented cacao taste different from raw beans.',
      'extraHtml' => ''
    ],
    [
      'term' => 'European-style drinking chocolate',
      'tag' => 'PRODUCT',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A rich drink made with chocolate or cocoa and usually milk or water. Some versions are thin, while others are thick because they contain more chocolate or a thickening ingredient.',
      'matter' => 'The name can describe different drinks, so the recipe and intended texture should be made clear.',
      'extraHtml' => ''
    ],
  ],
  'F' => [
    [
      'term' => 'Fat bloom',
      'tag' => 'QUALITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A pale, cloudy or streaky surface caused by fat movement or changes in fat crystals. Temperature swings, poor tempering and fat migration from fillings or inclusions can contribute.',
      'matter' => 'It affects appearance and may signal temperature, tempering or fat-migration issues that need investigating.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Fat migration',
      'tag' => 'STABILITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'Movement of fat from one part of a product into another, such as nut oil moving into a chocolate shell. It can soften the chocolate and contribute to fat bloom during storage.',
      'matter' => 'It can soften chocolate and cause defects, particularly when nut-rich or fat-rich fillings touch a chocolate shell.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Fermentation',
      'tag' => 'CACAO PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Microorganisms act on the pulp surrounding cacao beans after pods are opened. This creates flavour precursors and reduces raw-bean notes; how fermentation is managed strongly affects the final flavour.',
      'matter' => 'It is one of the most important stages for building the flavour potential of cacao beans.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Fine-flavour cacao',
      'tag' => 'CACAO QUALITY',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'Cacao valued for distinctive aromas and flavours beyond basic cocoa intensity. The description should be supported by the actual beans and their handling, not just an origin or variety name.',
      'matter' => 'The term should be supported by real flavour characteristics and reliable sourcing, not used as an automatic quality label.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Fluidity',
      'tag' => 'PROCESSING',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'How easily melted chocolate flows under particular conditions. It affects coating thickness and mould filling, and depends on cocoa butter, particle properties, emulsifiers and temperature.',
      'matter' => 'It determines whether chocolate can coat evenly, fill moulds and move through production equipment.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Freeze-dried fruit',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Fruit preserved by freezing and removing ice under low pressure, leaving a light, porous structure. It can add concentrated flavour and crunch to chocolate but readily absorbs moisture and may soften.',
      'matter' => 'Its crunch and flavour can be valuable in chocolate, but moisture protection is essential to maintain texture.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Forastero',
      'tag' => 'CACAO ORIGIN',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'The most widely cultivated cacao variety, accounting for 80-90% of global production. Forastero is hardier and more disease-resistant than Criollo, producing robust, classic cocoa flavours with higher bitterness and astringency.',
      'matter' => 'It forms the backbone of global chocolate production and is valued for its resilient yields and deep chocolate notes.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Form V (Beta-2)',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'The ideal crystalline polymorph of tempered cocoa butter. Melting at 33.8°C–34.2°C, Form V produces chocolate\'s signature glossy sheen, acoustic snap, silky oral melt, and bloom resistance.',
      'matter' => 'Achieving stable Form V crystals through precise tempering is what distinguishes artisan chocolate from soft, un-tempered chocolate.',
      'extraHtml' => ''
    ],
  ],
  'G' => [
    [
      'term' => 'Ganache',
      'tag' => 'PRODUCT',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A mixture usually made from chocolate and cream, sometimes with butter, sugar or flavourings. Ratios determine whether it is firm or soft, while water content and handling affect shelf life.',
      'matter' => 'Its ingredient ratio controls texture and its water content influences how it must be stored.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Gianduja',
      'tag' => 'PRODUCT',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A smooth mixture of chocolate and finely ground nuts, traditionally hazelnuts in the Italian style. Nut fat changes texture and flavour and can affect storage stability.',
      'matter' => 'The nut content changes both flavour and fat behaviour, making formulation and storage important.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Glucose syrup',
      'tag' => 'SWEETENER',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A syrup made by breaking starch into a mixture of sugars. It is often used in fillings and confectionery to influence sweetness, texture and crystallisation; it is not a simple one-for-one replacement for dry sugar in plain chocolate.',
      'matter' => 'It can help manage texture in fillings and confectionery, but it behaves differently from dry sugar.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Graininess',
      'tag' => 'TEXTURE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'A sandy or rough feeling in the mouth rather than a smooth melt. It can result from large particles, sugar crystals, moisture-related changes or an unsuitable formulation.',
      'matter' => 'It can reveal issues with particle size, crystallised sugar or moisture-related changes.',
      'extraHtml' => ''
    ],
  ],
  'H' => [
    [
      'term' => 'Hardness',
      'tag' => 'TEXTURE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'How much force is needed to bite, break or deform chocolate. It changes with temperature, cocoa butter crystal structure, recipe and added ingredients.',
      'matter' => 'It affects bite and handling, and can change as chocolate warms or its fat structure changes.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Hazelnut paste',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Hazelnuts ground until their natural oil is released and a paste forms. It is used in praline, gianduja and spreads; roasting, fineness and oil separation affect the result.',
      'matter' => 'It is a key ingredient in nut-based chocolate products, and its oil can influence firmness and separation.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Heat shock',
      'tag' => 'STABILITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A sudden or repeated temperature change that can disturb chocolate\'s fat crystal structure. Moving cold chocolate into humid air can also cause condensation, potentially leading to sugar bloom.',
      'matter' => 'It can damage the finish and structure of chocolate, especially when temperature changes are abrupt.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Humidity',
      'tag' => 'STORAGE',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'The amount of moisture in the surrounding air. High humidity can cause condensation on cold chocolate and may lead to sugar bloom, sticky surfaces or loss of crunch in moisture-sensitive inclusions.',
      'matter' => 'Moisture in the air can cause surface condensation and damage chocolate\'s appearance or crunchy inclusions.',
      'extraHtml' => ''
    ],
  ],
  'I' => [
    [
      'term' => 'Inclusion',
      'tag' => 'FORMULATION',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'An ingredient added as a distinct piece or particle, such as nuts, fruit, biscuit, seeds or cacao nibs. Its size, moisture, crunch and fat compatibility affect how well it works in chocolate.',
      'matter' => 'Inclusions bring texture and flavour, but their moisture and fat properties can affect the whole product.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Invert sugar',
      'tag' => 'SWEETENER',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A mixture of glucose and fructose formed when sucrose is split apart. It is more common in fillings and confectionery than plain chocolate, where it can influence moisture retention and crystallisation.',
      'matter' => 'It is useful in some fillings because it affects moisture retention and sugar crystallisation.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Ingredient declaration',
      'tag' => 'LABEL',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'The list of ingredients in a packaged product, usually in descending order by weight subject to local rules. Allergen statements and the naming of fats, sweeteners and chocolate types must also follow local requirements.',
      'matter' => 'Accurate ingredient and allergen information is essential for consumer understanding and regulatory compliance.',
      'extraHtml' => ''
    ],
  ],
  'J' => [
    [
      'term' => 'Jaggery',
      'tag' => 'SWEETENER',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A traditional sweetener made by concentrating sugarcane juice or palm sap. Its flavour, moisture and crystal structure differ from refined sugar, so it can change taste and processing in chocolate.',
      'matter' => 'Its distinctive flavour can be attractive in chocolate, but variable moisture and composition may affect processing.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Jelly or fruit-gel inclusion',
      'tag' => 'PRODUCT',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A fruit-based gel can add a soft, fruity centre to a chocolate product. Because it often contains water, it needs a suitable formulation and shelf-life assessment before being combined with chocolate.',
      'matter' => 'The water in fruit gels can affect shelf life and may migrate into adjacent chocolate or crunchy components.',
      'extraHtml' => ''
    ],
  ],
  'K' => [
    [
      'term' => 'Khandsari sugar',
      'tag' => 'SWEETENER',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A traditional Indian cane sugar generally less refined than white sugar. Colour, flavour, moisture and crystal size can vary, so it may not behave exactly like refined sucrose in chocolate.',
      'matter' => 'Its flavour and physical properties can differ from refined sugar, so recipe trials are important.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Kokum butter',
      'tag' => 'FAT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A vegetable fat extracted from kokum seeds. Its fat characteristics differ from cocoa butter, so suitability in chocolate or chocolate-style coatings depends on compatibility and applicable standards.',
      'matter' => 'It has different fat characteristics from cocoa butter, so compatibility and legal requirements must be checked.',
      'extraHtml' => ''
    ],
  ],
  'L' => [
    [
      'term' => 'Lecithin',
      'tag' => 'EMULSIFIER',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Often derived from soy or sunflower, lecithin is used in small amounts to influence chocolate flow. It can help particles move more easily in the fat phase, but more is not always better.',
      'matter' => 'It is used to tune flow, but the correct amount depends on the recipe and too much may not help.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Liquor (cocoa liquor)',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Another name for cacao mass: ground nibs containing cocoa solids and cocoa butter. “Liquor” refers to the paste-like material and does not mean alcohol.',
      'matter' => 'Knowing the term avoids confusion with alcoholic drinks and clarifies the ingredient\'s role in a recipe.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Low-sugar chocolate',
      'tag' => 'FORMULATION',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Chocolate made with less sugar than a chosen reference or with a different sweetening system. Reducing sugar can affect flow, texture, flavour and the accuracy of nutrition claims.',
      'matter' => 'Reducing sugar can change the product\'s structure as well as sweetness, so formulation and claims need care.',
      'extraHtml' => ''
    ],
  ],
  'M' => [
    [
      'term' => 'Maillard reaction',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'Reactions between certain sugars and amino compounds that create brown colour and roasted, nutty or complex flavours. They contribute to flavour development during cacao roasting and other heated food processes.',
      'matter' => 'It helps explain how heating creates some of the roasted and complex flavours associated with cacao.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Milk chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Chocolate made with cacao ingredients, sugar and milk ingredients within the rules of the market where it is sold. Milk ingredients affect flavour, colour, texture and melting behaviour.',
      'matter' => 'Milk ingredients change the taste and texture, and the product must meet the relevant market\'s definition.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Milk crumb',
      'tag' => 'INGREDIENT / PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'A dried mixture of milk and sugar used in some milk chocolate manufacturing, sometimes processed with cocoa ingredients. Heating and drying can create cooked-milk and caramel-like flavours.',
      'matter' => 'Its processing can contribute characteristic milk and caramel notes to some milk chocolates.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Moulding',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Filling moulds with chocolate so it sets into bars, shapes or shells. Temper, mould temperature, vibration and cooling influence shine, air bubbles and release from the mould.',
      'matter' => 'Correct moulding helps achieve clean shapes, smooth surfaces and fewer air bubbles or release defects.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Moisture migration',
      'tag' => 'STABILITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'Movement of water from one part of a product to another. Moisture can move from a soft filling or fruit inclusion into a drier component, making crunchy parts soft and affecting stability.',
      'matter' => 'It can make crisp inclusions soft and change the texture or stability of a filled chocolate.',
      'extraHtml' => ''
    ],
  ],
  'N' => [
    [
      'term' => 'Natural cocoa powder',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Cocoa powder that has not been alkalized. It is generally more acidic than Dutch-process cocoa, though exact flavour, colour and behaviour depend on the beans and processing.',
      'matter' => 'Its acidity differs from alkalized cocoa, which can affect flavour and recipe results.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Nib roasting',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Roasting cacao nibs after beans have been cracked and winnowed, rather than roasting whole beans. The two approaches expose cacao to heat differently and can create different flavour profiles.',
      'matter' => 'It gives makers a different way to control heat exposure and develop a desired flavour profile.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Non-dairy chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Chocolate formulated without dairy ingredients, using suitable plant-based ingredients where needed. Dairy-free or vegan claims require careful ingredient and cross-contact controls and must follow local rules.',
      'matter' => 'Ingredient and cross-contact controls matter when making dairy-free or vegan claims.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Nut paste',
      'tag' => 'INGREDIENT',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A paste made by grinding nuts until their natural oils are released. It adds flavour and softness to spreads and fillings, but its fat composition can affect firmness and oil separation.',
      'matter' => 'Its fineness and natural oil affect smoothness, firmness and the possibility of oil separation.',
      'extraHtml' => ''
    ],
  ],
  'O' => [
    [
      'term' => 'Origin',
      'tag' => 'CACAO SOURCING',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'Where cacao was grown, described by country, region, farm or cooperative. Specific origin claims need reliable traceability; flavour is also shaped by genetics, fermentation, drying and roasting.',
      'matter' => 'Origin information helps tell a cacao\'s story, but needs traceability and should not be treated as the sole predictor of flavour.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Oxidation',
      'tag' => 'STABILITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A chemical reaction involving oxygen that can change flavour, colour or quality over time. It is especially relevant to nuts and fat-rich ingredients, where it may lead to stale or rancid notes.',
      'matter' => 'It can cause stale or rancid flavours, particularly in nuts and other fat-rich ingredients.',
      'extraHtml' => ''
    ],
  ],
  'P' => [
    [
      'term' => 'Particle size',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'The size of solid particles such as sugar and cocoa in chocolate. Smaller particles can help create a smoother mouthfeel, but the full particle-size distribution also affects flow and texture.',
      'matter' => 'It strongly affects mouthfeel and can also influence how chocolate flows during production.',
      'extraHtml' => ''
    ],
    [
      'term' => 'pH',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A measure of how acidic or alkaline a water-based system is. It helps describe cocoa and fillings, but pH alone cannot tell you whether a product is safe or how long it will last.',
      'matter' => 'It helps describe acidity in cocoa and fillings, but cannot by itself establish safety or shelf life.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Polymorphism',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'The ability of a substance to form more than one crystal structure. Cocoa butter is polymorphic; different crystal forms melt and behave differently, making crystal control central to tempering.',
      'matter' => 'Different cocoa-butter crystal forms behave differently, which explains why tempering control matters.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Praline',
      'tag' => 'PRODUCT',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Depending on the country, praline can mean nuts coated in caramelised sugar and ground into paste, or a filled chocolate confection. Nut praline paste is often used in chocolate fillings.',
      'matter' => 'Because the word has different meanings in different places, specifying the style prevents confusion in recipes and product descriptions.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Pre-crystallisation',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'The controlled formation of suitable cocoa butter crystals before chocolate is moulded or coated. It is a key part of tempering and helps chocolate set with shine, snap and better resistance to bloom.',
      'matter' => 'It prepares chocolate to set properly and is central to achieving shine, snap and better bloom resistance.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Provenance',
      'tag' => 'SOURCING',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'The documented source and history of an ingredient, including where it came from and how it moved through the supply chain. It supports traceability, but only when backed by reliable records.',
      'matter' => 'Good provenance records help substantiate sourcing stories and connect ingredients to their documented origin.',
      'extraHtml' => ''
    ],
  ],
  'Q' => [
    [
      'term' => 'Quality in chocolate',
      'tag' => 'QUALITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'Quality is not defined by cocoa percentage alone. It can include clean flavour, good texture, suitable ingredients, consistent manufacture, traceability and fitness for the product\'s intended use.',
      'matter' => 'A broader view prevents misleading assumptions based only on price, origin or cocoa percentage.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Quetzalcoatl and cacao history',
      'tag' => 'HISTORY',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'Quetzalcoatl is a Mesoamerican deity sometimes linked to popular stories about cacao. Chocopedia should separate documented history from legends and later retellings, especially where the evidence or word origins are uncertain.',
      'matter' => 'It is important to distinguish historical evidence from legend when explaining cacao\'s cultural history.',
      'extraHtml' => ''
    ],
  ],
  'R' => [
    [
      'term' => 'Rancidity',
      'tag' => 'STABILITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'An unpleasant stale, paint-like or otherwise off flavour that develops as fats deteriorate. Nuts, seeds and other fat-rich inclusions need fresh ingredients and suitable storage to limit rancidity.',
      'matter' => 'It is an important shelf-life defect to monitor in chocolate products containing nuts, seeds or other fats.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Refining',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Reducing and controlling the size of solid particles in chocolate, often with roller refiners or other equipment. It improves smoothness and can influence flow, but it does not replace flavour development during fermentation and roasting.',
      'matter' => 'It improves smoothness and can change flow, both of which affect the finished product.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Roasting',
      'tag' => 'CACAO PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Heating cacao beans or nibs to develop aroma, reduce moisture and prepare them for further processing. Time, temperature and bean characteristics influence whether flavours become fruity, nutty, roasted or overly bitter.',
      'matter' => 'Roasting shapes aroma and flavour, so time and temperature must suit the beans and intended profile.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Ruby chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'The fourth type of chocolate (after dark, milk, white) launched in 2017. Made from specially selected \'ruby\' cacao beans, it has a natural pink hue and a berry-like, slightly tart flavour profile without added colourings or fruit flavouring.',
      'matter' => 'It introduces a distinct sensory profile and natural berry acidity without added fruit, expanding modern chocolate types.',
      'extraHtml' => ''
    ],
  ],
  'S' => [
    [
      'term' => 'Shelf life',
      'tag' => 'STABILITY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'The period a product is expected to remain safe and retain its intended quality under stated storage conditions. It should be established for the actual recipe and packaging, not copied from a similar product.',
      'matter' => 'It helps set realistic storage guidance and should be based on the actual product and packaging.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Snap',
      'tag' => 'SENSORY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'The crisp break heard or felt when solid chocolate is broken. A clean snap can suggest suitable structure and temper, but it is not by itself proof of overall quality.',
      'matter' => 'It is a familiar quality cue related to chocolate structure, though it should not be used as the only quality test.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Sucrose',
      'tag' => 'SWEETENER',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'Ordinary table sugar, made of glucose and fructose joined together. It is the main sweetener in many chocolate recipes and contributes bulk and particle structure as well as sweetness.',
      'matter' => 'It supplies sweetness and bulk, so changing it affects more than the sweetness level.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Sugar-free chocolate',
      'tag' => 'FORMULATION & LABEL',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Sugar-free is a regulated claim, not simply a way to describe chocolate with less sugar. A product must meet local rules, and alternative sweeteners can change texture, sweetness and digestive tolerance.',
      'matter' => 'The claim has a specific regulatory meaning and must not be confused with reduced sugar or no added sugar.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Sustainably sourced cacao',
      'tag' => 'SOURCING',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'A phrase suggesting attention to environmental or social concerns in cacao sourcing, but its meaning can vary. Clear claims should name the standard, programme or evidence behind them.',
      'matter' => 'Specific evidence makes this claim clearer and more credible than a broad, unexplained promise.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Sensory profile',
      'tag' => 'SENSORY',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-sensory',
      'definition' => 'A description of a chocolate\'s aromas, flavours, textures and aftertaste. Consistent terms and structured tasting make comparisons more useful than relying only on “good” or “bad.”',
      'matter' => 'A shared vocabulary helps tasters compare batches and communicate flavour more accurately.',
      'extraHtml' => ''
    ],
  ],
  'T' => [
    [
      'term' => 'Tempering',
      'tag' => 'PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Controlled heating and cooling encourages cocoa butter to form suitable crystals. Properly tempered chocolate usually sets with a glossy surface, clean snap and better resistance to fat bloom.',
      'matter' => 'It is essential for a reliable finish, snap and setting behaviour in many cocoa-butter chocolates.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Terroir',
      'tag' => 'CACAO ORIGIN',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'The combined influence of place, climate, soil, genetics and growing conditions on an agricultural product. In cacao these factors interact with fermentation, drying and roasting to shape flavour.',
      'matter' => 'It helps explain how growing conditions and place can contribute to cacao character, alongside processing.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Theobromine',
      'tag' => 'CACAO SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A naturally occurring compound in cacao, related to caffeine but with different effects. Its amount varies by cacao material and product; chocolate can affect people and animals differently.',
      'matter' => 'It is part of cacao\'s natural chemistry and is relevant when explaining how chocolate differs from other foods.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Traceability',
      'tag' => 'SOURCING',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'The ability to follow cacao or another ingredient through the supply chain. It helps support origin and sourcing claims, provided reliable records connect the finished product to its source.',
      'matter' => 'It supports more credible origin, sourcing and supply-chain claims when the records are reliable.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Trinitario',
      'tag' => 'CACAO ORIGIN',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'A hybrid cacao variety that originated in Trinidad in the 18th century from a natural cross between Criollo and Forastero. Trinitario combines Criollo\'s fine flavour aromatic complexity with Forastero\'s agronomic hardiness.',
      'matter' => 'It comprises 10-15% of world production and is prized by bean-to-bar chocolate makers for rich, fruity, and balanced chocolate profiles.',
      'extraHtml' => ''
    ],
  ],
  'U' => [
    [
      'term' => 'Unsweetened chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Chocolate made from cacao ingredients without added sugar, often used in baking or formulation. It is intense and bitter, so replacing sweetened chocolate with it usually requires recipe adjustments.',
      'matter' => 'It has no added sweetener, so recipes usually need adjustment when it replaces sweetened chocolate.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Upcycling cacao by-products',
      'tag' => 'SUSTAINABILITY',
      'category' => 'Bean',
      'tagClass' => 'tag-bean',
      'definition' => 'Finding useful applications for materials that might otherwise be discarded, such as suitable cacao pulp or pod-derived ingredients. Food safety, flavour, processing and local rules must be assessed before food use.',
      'matter' => 'It may reduce waste and create new ingredients, but food safety and suitability must be demonstrated.',
      'extraHtml' => ''
    ],
  ],
  'V' => [
    [
      'term' => 'Viscosity',
      'tag' => 'SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'How strongly a material resists flowing; higher-viscosity chocolate flows less easily under the same conditions. It affects moulding and coating and is influenced by temperature, fat, particles and emulsifiers.',
      'matter' => 'It helps predict how chocolate behaves in moulding, coating and depositing operations.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Vanillin',
      'tag' => 'FLAVOUR',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-ingredient',
      'definition' => 'A compound associated with vanilla aroma that may occur naturally or be produced by different methods. Ingredient source and accurate labelling matter when describing vanilla flavour.',
      'matter' => 'The source of vanilla flavour matters for accurate ingredient descriptions and labelling.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Volatile compounds',
      'tag' => 'FLAVOUR SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'Compounds that readily enter the air and contribute to aroma. Fermentation and roasting help create the mixture that gives cacao fruity, floral, nutty, spicy or roasted notes.',
      'matter' => 'They are a major part of aroma and help explain why fermentation and roasting change cacao\'s smell.',
      'extraHtml' => ''
    ],
  ],
  'W' => [
    [
      'term' => 'Water activity',
      'tag' => 'FOOD SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'A measure of how much water is available for microbial growth and certain reactions, rather than the total amount of water in food. It is especially useful for assessing fillings and products containing moist ingredients.',
      'matter' => 'It is especially important in fillings because it helps assess the water available for microbial growth and reactions.',
      'extraHtml' => ''
    ],
    [
      'term' => 'White chocolate',
      'tag' => 'CHOCOLATE TYPE',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'Made with cocoa butter, sugar and usually milk ingredients, but without the non-fat cocoa solids that give dark chocolate its brown colour. Legal definitions and required composition vary by market.',
      'matter' => 'Its composition differs from dark chocolate because it contains cocoa butter but not the non-fat cocoa solids.',
      'extraHtml' => ''
    ],
    [
      'term' => 'Winnowing',
      'tag' => 'CACAO PROCESS',
      'category' => 'Process,Technique',
      'tagClass' => 'tag-process',
      'definition' => 'Separating the thin outer shell from cracked cacao beans to leave the nibs used for chocolate. Effective winnowing helps reduce shell fragments in the finished product.',
      'matter' => 'Removing shells helps ensure the nibs used for chocolate are cleaner and more consistent.',
      'extraHtml' => ''
    ],
  ],
  'X' => [
    [
      'term' => 'Xocoatl (historical term)',
      'tag' => 'HISTORY',
      'category' => 'Bean',
      'tagClass' => 'tag-type',
      'definition' => 'A term often mentioned in popular accounts of early chocolate drinks, although its historical use and spelling are debated. Separate documented history from modern retellings and uncertain word origins.',
      'matter' => 'Careful wording prevents uncertain historical claims from being presented as settled fact.',
      'extraHtml' => ''
    ],
  ],
  'Y' => [
    [
      'term' => 'Yeast in cacao fermentation',
      'tag' => 'CACAO SCIENCE',
      'category' => 'Science,Ingredient',
      'tagClass' => 'tag-science',
      'definition' => 'Yeasts use sugars in the pulp surrounding cacao beans during fermentation. Along with bacteria and other microbes, they help create the conditions and flavour precursors that shape the beans.',
      'matter' => 'Yeasts are part of the microbial community that creates the flavour precursors needed for chocolate flavour.',
      'extraHtml' => ''
    ],
  ],
  'Z' => [
    [
      'term' => 'Zero-sugar claim',
      'tag' => 'LABEL & REGULATION',
      'category' => 'Type',
      'tagClass' => 'tag-type',
      'definition' => 'A zero-sugar claim can only be used when a product meets the nutrition-labelling rules in its target market. It is not the same as “reduced sugar” or “no added sugar.”',
      'matter' => 'Using the correct claim protects consumers from confusion and helps avoid inaccurate labelling.',
      'extraHtml' => ''
    ],
  ],
];
$allTermsCount = 104;
$activeLetters = array_keys($glossary);

include 'includes/header.php';
?>

<!-- --- CHOCOPEDIA PAGE WRAPPER --- -->
<div id="page-chocopedia" class="page active" style="padding-top: 100px;">

  <!-- CHOCOPEDIA HERO -->
  <section class="chocopedia-hero" style="padding-top: 24px !important;">
    <div class="section-label" style="text-align: center; margin-bottom: 12px; display: inline-block;">📖 A–Z Chocolate Dictionary</div>
    <h1 style="margin-top: 6px !important;">Chocopedia</h1>
    <p>A practical reference to cacao, chocolate-making, ingredients, flavour and chocolate science — explained in simple language.</p>
    
    <div class="chocopedia-search">
      <span class="search-icon">
        <svg width="18" height="18" style="width:18px; height:18px; min-width:18px; max-width:18px; display:block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </span>
      <input type="text" id="chocopedia-search-input" placeholder="Search terms (e.g. acidity, conching, criollo, tempering)..." oninput="handleSearchInput(this.value)" autocomplete="off">
      <button type="button" id="search-clear-btn" class="search-clear-btn" onclick="clearSearchInput()" aria-label="Clear Search">✕</button>
    </div>
  </section>

  <!-- CATEGORIES & GLOSSARY CONTAINER -->
  <div class="section" style="padding-top: 14px;">
    <div class="section-label" style="text-align: center;">Browse by Category</div>
    <h2 class="section-title" style="text-align: center; margin-bottom: 32px;">Explore the World of Chocolate</h2>

    <!-- Category Filter Cards -->
    <div class="chocopedia-categories">
      <div class="chocopedia-cat-card" data-category="Bean" onclick="toggleCategoryFilter('Bean', this)">
        <div class="chocopedia-cat-icon">🫘</div>
        <h3>Bean Origins</h3>
        <p>Cacao varietals, terroir, harvesting, botany, and agroforestry sourcing.</p>
      </div>
      <div class="chocopedia-cat-card" data-category="Process,Technique" onclick="toggleCategoryFilter('Process,Technique', this)">
        <div class="chocopedia-cat-icon">⚗️</div>
        <h3>Craft &amp; Process</h3>
        <p>From fermentation, drying, and roasting to conching and tempering.</p>
      </div>
      <div class="chocopedia-cat-card" data-category="Type" onclick="toggleCategoryFilter('Type', this)">
        <div class="chocopedia-cat-icon">🍫</div>
        <h3>Chocolate Types</h3>
        <p>Dark, milk, white, ruby, bonbons, couverture, and confections.</p>
      </div>
      <div class="chocopedia-cat-card" data-category="Science,Ingredient" onclick="toggleCategoryFilter('Science,Ingredient', this)">
        <div class="chocopedia-cat-icon">🔬</div>
        <h3>Food Science</h3>
        <p>Crystals, polymorphism, bloom, fats, sweeteners, and sensory chemistry.</p>
      </div>
    </div>

    <!-- Active Filter Feedback Banner -->
    <div id="chocopedia-filter-banner" class="chocopedia-filter-banner" style="display: none;">
      <span class="filter-banner-text" id="filter-banner-text">Showing filtered terms</span>
      <button type="button" class="filter-banner-reset" onclick="resetChocopediaFilters()">Clear Filter (Show All <?= $allTermsCount ?> Terms)</button>
    </div>

    <!-- Alphabet Quick Navigation Bar -->
    <nav class="chocopedia-alpha-nav" aria-label="Alphabet Jump Navigation">
      <span class="alpha-nav-label">A–Z Index:</span>
      <button type="button" class="alpha-nav-chip active" onclick="scrollToLetter('ALL')">All</button>
      <?php foreach (range('A', 'Z') as $char):
        $hasLetter = in_array($char, $activeLetters);
      ?>
        <button type="button" class="alpha-nav-chip <?= $hasLetter ? '' : 'disabled' ?>" <?= $hasLetter ? 'onclick="scrollToLetter(\''.$char.'\')"' : '' ?>><?= $char ?></button>
      <?php endforeach; ?>
    </nav>

    <!-- Empty State -->
    <div id="chocopedia-empty-state" class="chocopedia-empty-state" style="display: none;">
      <div class="empty-icon">🔍</div>
      <h3>No Chocolate Terms Found</h3>
      <p id="empty-query-msg">We couldn't find any terms matching your criteria.</p>
      <button type="button" class="btn-clear-search" onclick="resetChocopediaFilters()">Reset Search &amp; Show All <?= $allTermsCount ?> Terms</button>
    </div>

    <!-- GLOSSARY LIST -->
    <div class="chocopedia-glossary" id="chocopedia-glossary">
      <?php foreach ($glossary as $letter => $terms): ?>
        <div class="chocopedia-letter-group" data-letter="<?= $letter ?>">
          <div class="chocopedia-letter" id="letter-<?= $letter ?>"><?= $letter ?> <span class="chocopedia-letter-count">(<?= count($terms) ?>)</span></div>
          <div class="chocopedia-terms-list">
            <?php foreach ($terms as $t): ?>
              <div class="chocopedia-term" data-term="<?= htmlspecialchars(strtolower($t['term'])) ?>" data-category="<?= htmlspecialchars($t['category']) ?>">
                <div class="chocopedia-term-header">
                  <h4><?= htmlspecialchars($t['term']) ?></h4>
                  <span class="chocopedia-term-tag <?= $t['tagClass'] ?>"><?= htmlspecialchars($t['tag']) ?></span>
                </div>
                <p><?= htmlspecialchars($t['definition']) ?></p>
                <?php if (!empty($t['matter'])): ?>
                  <div class="chocopedia-term-matter">
                    <strong>Why it matters:</strong> <?= htmlspecialchars($t['matter']) ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($t['extraHtml'])): ?>
                  <?= $t['extraHtml'] ?>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
let currentCategoryFilter = null;
let currentSearchQuery = '';

function handleSearchInput(val) {
  currentSearchQuery = val.trim();
  const clearBtn = document.getElementById('search-clear-btn');
  if (clearBtn) {
    clearBtn.style.display = currentSearchQuery ? 'flex' : 'none';
  }
  applyFilters();
}

function clearSearchInput() {
  const input = document.getElementById('chocopedia-search-input');
  if (input) input.value = '';
  currentSearchQuery = '';
  const clearBtn = document.getElementById('search-clear-btn');
  if (clearBtn) clearBtn.style.display = 'none';
  applyFilters();
}

function toggleCategoryFilter(catStr, cardEl) {
  const cats = catStr.split(',');
  const isCurrentlyActive = cardEl.classList.contains('active');

  // Deactivate all cards first
  document.querySelectorAll('.chocopedia-cat-card').forEach(c => c.classList.remove('active'));

  if (isCurrentlyActive) {
    currentCategoryFilter = null;
  } else {
    cardEl.classList.add('active');
    currentCategoryFilter = cats;
    
    // Smooth scroll down to glossary list
    const glossary = document.getElementById('chocopedia-glossary');
    if (glossary) {
      const topOffset = glossary.getBoundingClientRect().top + window.pageYOffset - 110;
      window.scrollTo({ top: topOffset, behavior: 'smooth' });
    }
  }
  applyFilters();
}

function resetChocopediaFilters() {
  clearSearchInput();
  document.querySelectorAll('.chocopedia-cat-card').forEach(c => c.classList.remove('active'));
  currentCategoryFilter = null;
  applyFilters();
}

function applyFilters() {
  const query = currentSearchQuery.toLowerCase();
  const groups = document.querySelectorAll('.chocopedia-letter-group');
  let totalVisible = 0;

  groups.forEach(group => {
    const terms = group.querySelectorAll('.chocopedia-term');
    let groupVisibleCount = 0;

    terms.forEach(term => {
      const text = term.textContent.toLowerCase();
      const termCat = term.getAttribute('data-category') || '';
      const catList = termCat.split(',').map(c => c.trim());
      
      const matchesSearch = !query || text.includes(query);
      const matchesCategory = !currentCategoryFilter || currentCategoryFilter.some(fc => catList.includes(fc));

      const isMatch = matchesSearch && matchesCategory;
      term.style.display = isMatch ? '' : 'none';
      if (isMatch) {
        groupVisibleCount++;
        totalVisible++;
      }
    });

    group.style.display = groupVisibleCount > 0 ? '' : 'none';
    const countBadge = group.querySelector('.chocopedia-letter-count');
    if (countBadge) {
      countBadge.textContent = `(${groupVisibleCount})`;
    }
  });

  // Empty state handling
  const emptyState = document.getElementById('chocopedia-empty-state');
  const emptyMsg = document.getElementById('empty-query-msg');
  const glossary = document.getElementById('chocopedia-glossary');

  if (totalVisible === 0) {
    if (emptyState) emptyState.style.display = 'block';
    if (emptyMsg) {
      if (currentSearchQuery && currentCategoryFilter) {
        emptyMsg.textContent = `No terms matching "${currentSearchQuery}" in the selected category.`;
      } else if (currentSearchQuery) {
        emptyMsg.textContent = `No chocolate terms found matching "${currentSearchQuery}".`;
      } else {
        emptyMsg.textContent = `No terms found for this filter.`;
      }
    }
    if (glossary) glossary.style.display = 'none';
  } else {
    if (emptyState) emptyState.style.display = 'none';
    if (glossary) glossary.style.display = '';
  }

  // Filter banner update
  const banner = document.getElementById('chocopedia-filter-banner');
  const bannerText = document.getElementById('filter-banner-text');
  if (banner && bannerText) {
    if (currentCategoryFilter || currentSearchQuery) {
      banner.style.display = 'flex';
      let msg = `Showing ${totalVisible} term${totalVisible !== 1 ? 's' : ''}`;
      if (currentCategoryFilter) {
        msg += ` in category filter`;
      }
      if (currentSearchQuery) {
        msg += ` for "${currentSearchQuery}"`;
      }
      bannerText.textContent = msg;
    } else {
      banner.style.display = 'none';
    }
  }

  // Update Alphabet Chips active / disabled state
  updateAlphaChipsState(groups);
}

function updateAlphaChipsState(groups) {
  document.querySelectorAll('.alpha-nav-chip:not([onclick*="ALL"])').forEach(chip => {
    const letter = chip.textContent.trim();
    const grp = document.querySelector(`.chocopedia-letter-group[data-letter="${letter}"]`);
    if (grp && grp.style.display !== 'none') {
      chip.classList.remove('disabled');
    } else {
      chip.classList.add('disabled');
    }
  });
}

function scrollToLetter(letter) {
  if (letter === 'ALL') {
    resetChocopediaFilters();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  const el = document.getElementById('letter-' + letter);
  if (el) {
    const topOffset = el.getBoundingClientRect().top + window.pageYOffset - 95;
    window.scrollTo({ top: topOffset, behavior: 'smooth' });
  }
}
</script>

<?php include 'includes/footer.php'; ?>
