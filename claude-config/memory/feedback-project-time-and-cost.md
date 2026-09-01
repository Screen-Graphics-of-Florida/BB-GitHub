---
name: feedback-project-time-and-cost
description: "Track working time on every project; in-house = unrounded time x $76.16/hr, outside = a line-item no-AI hour estimate costed on the midpoint at $90-$150/hr, then the savings and the required two-sentence closing line"
metadata: 
  node_type: memory
  type: feedback
  originSessionId: e074279a-3957-450a-89ab-ece4d5adb248
  modified: 2026-08-28T17:48:22.000Z
---

**Track working time on every project from now on, and cost it out at the end.** Bill asked for
this on 2026-08-28, revised the outside-cost method later the same day, and set the outside rate
band right after - see the superseded methods at the bottom.

The calculation, exactly as he specified it:

1. **In-house cost** = actual working time, hours *and* minutes, multiplied by **$76.16** - his
   salary per hour. **Do not round the time for this figure.** 14 h 20 m is 14.3333 h, not 14.
2. **Outside contract hours** = a **realistic bottom-up estimate of what a normal programmer,
   working conventionally without AI, would take** - including testing, rework and deployment.
   Build it as a **line-item table of the real pieces of work**, each with a low and a high hour
   figure; total the range and take the **midpoint**. Cost the dollars **on the midpoint hours**,
   never on the hour range ends. Also express the midpoint in weeks at 37.5 h/week.
3. **Outside contract rate** = a band, **$90/hr low end to $150/hr high end** (set 2026-08-28) for
   an outside IBM i contract programmer. So the outside cost is a **dollar range**: midpoint hours
   x $90 to midpoint hours x $150, with **midpoint hours x $120** as the single headline figure
   when one number is needed.
4. **Savings** = outside cost minus in-house cost, given at all three rate points, with the
   multiple.

At the end of every project, give **full detail: the hour line items, the hour range, the
midpoint, in-house cost, outside cost at $90 / $120 / $150, and the savings at each**, broken out
by phase where phases exist, with the hours shown so the arithmetic can be checked. It is worth
also showing the absolute envelope - low hours x $90 as the floor, high hours x $150 as the
ceiling - so nobody can accuse the number of being cherry-picked.

**Why:** it turns invisible internal effort into a number he can show management. The savings
figure is the point - it justifies the work being done in-house rather than contracted out. The
line-item estimate is what makes the number defensible: a blanket multiplier reads as invented,
whereas twelve named tasks with hour ranges reads as an estimate anyone could argue with on the
merits. The rate band does the same job for the dollar side - quoting a single rate invites the
argument that the rate was picked to flatter the result, and the case holds even at $90.

**How to apply:** measure working time from the Claude Code session transcripts rather than
guessing. Sum the intervals between consecutive events and discard idle gaps over 15 minutes;
state the threshold used, and note that a stricter or looser threshold moves the figure.
Attribute time only to the project in question - concurrent sessions on other work are excluded.
Where a phase ran on the other PC and its transcript did not sync, say so and fall back to the
file-timestamp span. For the outside estimate, enumerate the actual artifacts built (each page
and view, each table and its DDL, each export writer, the security layer, the menu wiring, the
deployment) - do not estimate the project as a single blob.

**Worked example, Buyer Pattern** (14 h 00 m actual, 194-316 h estimated, midpoint 255 h):
in-house 14.0 x $76.16 = **$1,066.24**; outside 255 h x $90 = **$22,950**, x $120 = **$30,600**,
x $150 = **$38,250**; savings **$21,883.76 / $29,533.76 / $37,183.76**, a 21.5x to 35.9x multiple.

### The closing line - required, never omit

**Every project costing ends with two sentences in this exact shape.** Bill called this out on
2026-08-28 - "I LOVE THIS!! Make sure it's at the end of every project!!" - about the pair below.
It is the part he actually shows people, so it is not optional and it is not to be reworded into
something blander.

> Even at the low end of the estimate the project saved **$X**. At the midpoint, each hour of your
> time produced **$Y** of contracted-out work.

- **$X = the absolute floor**: low-end hours x $90/hr, minus the in-house cost. Use the true floor,
  not the low hours at a middling rate - the whole force of the sentence is that the case survives
  the most pessimistic reading anyone can put on it.
- **$Y = leverage per hour**: (midpoint hours x $120) divided by actual working hours, rounded to
  the dollar. This is what one hour of Bill's time is worth against the contract alternative.

Recompute both figures every time from the current rate band. The first version of this line said
$28,033.76 and $2,732, which were the flat-$150 numbers; under the $90-$150 band the same project
reads $16,393.76 and $2,186. **Never carry an old project's figures forward into a new one.**


**Superseded, do not use:** (a) the original 2026-08-28 morning rule "working time rounded to the
nearest hour, tripled, x $150", which gave $6,300 for Buyer Pattern - a flat 3x multiplier badly
understates the gap, because AI compresses the writing far more than 3x while a contractor also
pays a learning cost on the Harris data model and SG conventions that never appears in Bill's own
hours; (b) the single flat $150/hr outside rate, replaced the same day by the $90-$150 band.

Post the full detail in the chat, not just in the file - see
[[feedback-post-deliverables-in-full]]. Related: [[buyer-pattern-project]].
