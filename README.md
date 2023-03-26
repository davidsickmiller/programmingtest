2023-03-25

Notes taken concurrently

1:44pm start

It's tempting to write the test in C#, since that's most relevant for the company.  But it's been several years since I've last written C#.  I don't have the tooling set up on my laptop, and with the test being only 2 hours there's risk that I'll run into some setup problem or error message that takes 20-30 minutes to address, at which point I will have lost a quarter of the test time.  In skimming through the implementation steps, I don't see anything that would be hugely different in C# versus, say, PHP or Python.  While I'll definitely need to learn more about C# for the role, I'd imagine it's more likely stuff like optimizing cold start times or smartly upgrading .NET Framework versions that will be important to understand.

1:51
Created a repo: https://github.com/davidsickmiller/programmingtest
Cloned it.
Opened it in PHPStorm

> 1) The initial implementation just needs to calculate cost based on a parcel’s size. For each size category there is a fixed delivery cost
>
> ○ Small parcel: all dimensions < 10cm. Cost $3
> 
> ○ Medium parcel: all dimensions < 50cm. Cost $8
> 
> ○ Large parcel: all dimensions < 100cm. Cost $15
> 
> ○ XL parcel: any dimension >= 100cm. Cost $25

Floating point should be fine for dimensions, particularly since we're only talking about a single parcel and are simply adding the dimensions together.
Integer should be fine for cost at this point.

I wonder how we should interpret "all dimensions" and "any dimension".  Airlines do a thing where they add up the width, height, and length and compare that sum to a max size.  I'm going to assume that "all dimensions" refers to that sum of W + H + L, whereas "any dimension" refers to the longest single dimension.

Are there any situations where a parcel falls into multiple categories?  Small, medium, and large seem straightforward.  But, say, how would we handle a parcel that's 75cm x 75cm x 75cm?  The total of the three dimensions is 225 which wouldn't qualify as a Large parcel (since 225 is not under 100cm).  But it doesn't qualify as a XL parcel, because no individual dimension is over 100cm.

OK, let's change my assumption.  If both "all dimensions" and "any dimension" essentially mean the same thing (i.e. longest single dimension), we no longer have parcel sizes that don't belong to a category.


2:14 OK, I have a function and a test.  Now wrestling with setting up PHPUnit...
2:15 Skipped setting up autoloading by just having the test class use `require`.  Test passes, commiting...

Oh, looks like I missed something: While I remembered "The input can be in any form you choose", I missed "Output should be a collection of items with their individual cost and type, as well as the total cost.

So, at a minimum, the input needs to be an array (or array-like object).  I wonder if I should just make an array of dimensions or if I should include some sort of item identifier.  Or I could introduce an Item class and have the function return Item objects with cost attributes set on them.  The best approach probably depends on the context, which I don't really have.  Maybe I'll use an Item class, to demonstrate that even though I'm using PHP, I do know how to create classes.

Actually, if I'm going to have multiple classes, I really should set up autoloading properly.

2:30pm committed autoloading setup. Back to introducing the Item class...

2:36pm Oh, I added cost but it looks like I should also add 'type'.  Let's do that next...DONE

2:42pm OK, let's make it a collection and add in the total cost... DONE

2:51pm Let's re-read before proceeding to Step 2... I don't spot anything wrong.  On to the next step!

> 2) Thanks to logistics improvements we can deliver parcels faster. This means we can charge more money. Speedy shipping can be selected by the user to take advantage of our improvements.
>
> ○ Speedy shipping doubles the cost of the entire order
> 
> ○ Speedy shipping should be listed as a separate item in the output, with its associated cost
> 
> ○ Speedy shipping should not impact the price of individual parcels, i.e. their individual cost should remain the same as it was before

It looks like speedy shipping is an order-level parameter, not an item-level parameter.

I wonder if I should replace the Item[] array with an Order object.  Maybe defer that for now...

But if I defer it, I need to decide how to tell calculateCost() whether speedy shipping has been selected.  I could add a boolean parameter for that, but I hate boolean parameters for the sake of readability.  Maybe I'll start with a ShippingType enum.

3:04pm OK, I've implemented speedy shipping.  But I've kinda bastardized my Item class because it's full of optional parameters.  I could introduce a subclass ItemWithPhysicalDimensions but that seems overkill.  Maybe an OrderLineItem class, potentially linked to a Parcel class.  But for now, I think I'll just move on.

> 3) There have been complaints from delivery drivers that people are taking advantage of our dimension only shipping costs. A new weight limit has been added for each parcel type, over which a charge per kg applies
>
> +$2/kg over weight limit for parcel size:
> 
> ● Small parcel: 1kg
> 
> ● Medium parcel: 3kg
> 
> ● Large parcel: 6kg
> 
> ● XL parcel: 10kg

Do we double the overweight fee if it's speedying shipping?  I will assume yes.

What do we do for partial kilograms over the limit?  I will assume we take the ceiling.  E.g. 0.1 kg over counts as 1 kg over.


3:27pm

> 4) Some of the extra weight charges for certain goods were excessive. A new parcel type has been added to try and address overweight parcels
>
> Heavy parcel, $50 up to 50kg +$1/kg over 50kg


OK, it seems important that we obey this rule from the beginning: "In all circumstances the cheapest option for sending each parcel should be selected"

Theoretically a parcel of any size could be a heavy parcel.  So we'll need to check all of them.

3:37pm

> 5) In order to award those who send multiple parcels, special discounts have been introduced.
>
> ● Small parcel mania! Every 4th small parcel in an order is free!
> 
> ● Medium parcel mania! Every 3rd medium parcel in an order is free!
> 
> ● Mixed parcel mania! Every 5th parcel in an order is free!
>
> 
> ● Each parcel can only be used in a discount once
> 
> ● Within each discount, the cheapest parcel is the free one
> 
> ● The combination of discounts which saves the most money should be selected every time
>
> Example:
> 
> 6x medium parcels. 3x $8, 3 x $10. 1st discount should include all 3 $8 parcels and save $8.
> 
> 2nd discount should include all 3 $10 parcels and save $10.
> 
> ● Just like speedy shipping, discounts should be listed as a separate item in the output, with associated saving, e.g. “-$2”
> 
> ● Discounts should not impact the price of individual parcels, i.e. their individual cost should remain the same as it was before
> 
> ● Speedy shipping applies after discounts are taken into account
>

I assume a parcel being free means only that the parcel's shipping is free.

I probably won't complete this in the next 8 minutes.

It sounds like "Small parcel mania" and "Mixed parcel mania" could both be applied to an order, as long as we don't violate the "Each parcel can only be used in a discount once" rule.

For very large orders, it seems like this could be an expensive calculation, as an individual parcel could be associated with any of the three discounts.  One way this count be limited is to only look at the 25% of small, 33% of medium, and 20% of all parcels that are discount-eligible.  Perhaps there's some sort of characteristic (e.g. discount 1 is always cheaper than discount 2) that allow us to be less brute-force about it, but such a characteristic doesn't jump out at me.

3:52 Let's stop here, since I'm slightly over 2 hours now.


Output of PHPUnit:
<img width="1840" alt="Screenshot 2023-03-25 at 3 58 19 PM" src="https://user-images.githubusercontent.com/172005/227686620-5b70bd1a-80ca-4bed-a55f-4396a58b4d09.png">

--

After the time limit, I implemented Step 5 in a branch here: https://github.com/davidsickmiller/programmingtest/pull/1
