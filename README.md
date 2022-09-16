# RFM Analysis - be.cncd.cncdrfm

This extension calculates the recency, frequency and monetary value (RFM) of all donors.

## API: Contact.Calculatefrm

Calculating the RFM is a heavy database operation. That's why we don't do this in real-time.

The API Contact.Calculaterfm calculates the RFM for all donors for a specific reference year.

The values are stored in multi-custom field set for each contact.

The idea is to run this API once for all the previous years, and schedule it daily or weekly for the current year.

If no reference year is passed, the API will take the current year. Making it easy to "set and forget" a scheduled job daily or weekly.

## Custom Fields

The custom field set (a.k.a. custom group) "RFM" is used to store the RFM data for a specific year.

It contains the fields:

 * reference year
 * Is new donor? (it will be "Yes" if the contact never made a donation of any kind)
 * the recency, expressed as "NRG". Which stands for "non-régulier", and is expressed a three digit code (see further)
 * frequency, which is the number of donations done in the reference year
 * the average monetary value of the donations
 * the monetary value, which is the sum of each donation for that year

## Tab "RFM" on Contact Summary

The RFM custom fiields are shown as an extra Tab on the contact screen.

## Report: Analyse RFM

There is a report "Analyse RFM" that displays a RFM summary table.

In the filters you can specify the reference year.

A menu item under Reports has been created for direct access to this report.

## NRG = recency code

The recency is expressed as a three digit code.

Each digit stands for a specific year. From RIGHT to LEFT:

 * last year
 * 2 years ago
 * 3 years ago

These are the possibilities and their meaning:

 * 000: made no donation last year, 2 years ago, or 3 years ago
 * 001: made one or more donations last year, but not 2 years ago, or 3 years ago
 * 010: made no donation last year, one or more donations 2 years ago, but no donations 3 years ago
 * 100: made no donation last year, no donations 2 years ago, but made one or more donations 3 years ago
 * 011: made one or more donations last year and one or more donations 2 years ago, but no donations 3 years ago
 * 101: made one or more donations last year, no donations 2 years ago, but one or more donations 3 years ago
 * etc...

Thus, the NRG code contains only information about the previous 3 years. It does not say anything about the reference year.
A person falling in the e.g. 010 segment, may or may have not donated in the reference year.
Look at the fields frequency, average amount and total for a detailed view of the donor for that reference year.

Some examples:

* say the reference year is 2019, the NRG = 000, and the frequncy is 2: this a contact who made 2 donations donated in 2019 but no donations in the three years before.
* in 2020, the above contact will have an NRG = 001. If the frequency is > 0, he made one or more donations in 2020.

## Pseudo-recurring Donors

With a SEPA mandate, a donor can donate a specific amount on a monthly basis.

But it can happen that these SEPA donations are not recognized as such, and are stored as ad-hoc contributions.

The API has a built-in conversion mechanism that converts 12 donations of the same amount registered as "don", "don pour une campagne"... as "Don récurrent".

## Screen for Admins

Admins can go to civicrm/cncdrfm-generate?reset=1 to regenerate the RFM data for:

 * a specific year
 * or for a specific contact for all years


