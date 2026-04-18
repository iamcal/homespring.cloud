#!/usr/bin/env python3
"""Render the entire tbody for examples.htm from tests/results.json."""
import json

RESULTS = '/mnt/webroot/homespring.cloud/tests/results.json'

SECTIONS = [
    ('Jeff Binder', '— 2003 · author of Homespring and its <a href="https://github.com/iamcal/Homespring">original Scheme interpreter</a>', [
        ('jeff-add',     'examples/2003-jeff-binder/add.hs',     'add.hs',     'Reads two numbers from input and outputs their sum.'),
        ('jeff-cat',     'examples/2003-jeff-binder/cat.hs',     'cat.hs',     'Echoes its input straight to output, like the Unix <code>cat</code> utility.'),
        ('jeff-first',   'examples/2003-jeff-binder/first.hs',   'first.hs',   'A minimal "Hello, world" — a single hatchery feeding a bear.'),
        ('jeff-hello-1', 'examples/2003-jeff-binder/hello-1.hs', 'hello-1.hs', '"Hello, World!" using a universe, a hatchery, and snowmelt-powered rapids.'),
        ('jeff-hello-2', 'examples/2003-jeff-binder/hello-2.hs', 'hello-2.hs', '"Hello, World!" — an alternative arrangement using the power-override rule.'),
        ('jeff-hello-3', 'examples/2003-jeff-binder/hello-3.hs', 'hello-3.hs', '"Hello, World!" using marshy force, field-sense shallows, and a hydro-power spring.'),
        ('jeff-hi',      'examples/2003-jeff-binder/hi.hs',      'hi.hs',      'Prompts the user for their name and greets them back with a "Hi".'),
        ('jeff-name',    'examples/2003-jeff-binder/name.hs',    'name.hs',    'An acrostic program whose node names spell out HOMESPRING line by line.'),
        ('jeff-null',    'examples/2003-jeff-binder/null.hs',    'null.hs',    'The empty program — zero bytes, does nothing.'),
        ('jeff-quiz',    'examples/2003-jeff-binder/quiz.hs',    'quiz.hs',    'A tiny maths quiz that asks "what\'s six times four?" and grades the reply.'),
        ('jeff-simple',  'examples/2003-jeff-binder/simple.hs',  'simple.hs',  'The simplest possible non-empty program — a single node.'),
    ]),
    ('Cal Henderson', '— 2003 · author of the <a href="https://github.com/iamcal/perl-Language-Homespring">Perl interpreter</a>', [
        ('cal-hello2',   'examples/2003-cal-henderson/hello2.hs','hello2.hs',  'A compact "Hello, World!" — shipped with the Perl interpreter as a test program.'),
    ]),
    ('Joe Neeman', '— 2005 · author of the <a href="https://github.com/jneem/homespring">OCaml interpreter</a>', [
        ('joe-flipflop', 'examples/2005-joe-neeman/flipflop.hs', 'flipflop.hs','A flip-flop that alternates state using an inverse-lock and a pair of pump/switch nodes.'),
        ('joe-tic',      'examples/2005-joe-neeman/tic.hs',      'tic.hs',     'A tic-tac-toe implementation — the most elaborate program in the collection.'),
    ]),
    ('Quin Kennedy', '— 2012 · author of a <a href="https://github.com/quinkennedy/Homespring">JavaScript interpreter</a>', [
        ('quin-reverse',   'examples/2012-quin-kennedy/reverse.hsg',   'reverse.hsg',   'Reverses the input using a <code>force.up</code> node and a <code>split</code>.'),
        ('quin-reverse-2', 'examples/2012-quin-kennedy/reverse-2.hsg', 'reverse-2.hsg', 'Reverses the input — a more poetic variant ("split wings calm the ebb and flow").'),
        ('quin-reverse-3', 'examples/2012-quin-kennedy/reverse-3.hsg', 'reverse-3.hsg', 'Reverses the input — a third variation of the same arrangement.'),
        ('quin-split',     'examples/2012-quin-kennedy/split.hsg',     'split.hsg',     'Splits input into pieces.'),
    ]),
    ('Benito van der Zander', '— 2013 · author of the <a href="https://github.com/benibela/home-river">HomeSpringTree compiler</a>; most are generated from HomeSpringTree (.hst) sources', [
        ('benito-clock',    'examples/2013-benito-van-der-zander/clock.hs',    'clock.hs',    'A ticking clock driven by the <code>time</code> node and a range-switch cascade.'),
        ('benito-count',    'examples/2013-benito-van-der-zander/count.hs',    'count.hs',    'Counts from 0 to 9 and then to 100 using a bridged bear and hatchery cascade.'),
        ('benito-count2',   'examples/2013-benito-van-der-zander/count2.hs',   'count2.hs',   'A more elaborate counter built from digit generators.'),
        ('benito-count3',   'examples/2013-benito-van-der-zander/count3.hs',   'count3.hs',   'Another counter variant.'),
        ('benito-count4',   'examples/2013-benito-van-der-zander/count4.hs',   'count4.hs',   'A larger counter implementation (~10 KB of source).'),
        ('benito-count-poem',            'examples/2013-benito-van-der-zander/count.poem.hs',            'count.poem.hs',            'A counter written in the poetic style Homespring is intended to be read in.'),
        ('benito-count-poem-withfillers','examples/2013-benito-van-der-zander/count.poem.withfillers.hs','count.poem.withfillers.hs','A poetic counter with additional filler words for extra flow.'),
        ('benito-fizzbuzz',       'examples/2013-benito-van-der-zander/fizzbuzz.hs',       'fizzbuzz.hs',       'Classic FizzBuzz — prints Fizz for multiples of 3, Buzz for 5, FizzBuzz for both.'),
        ('benito-fizzbuzz-poem',  'examples/2013-benito-van-der-zander/fizzbuzz.poem.hs',  'fizzbuzz.poem.hs',  'FizzBuzz written in the poetic style — considerably longer.'),
        ('benito-fizzbuzztick',   'examples/2013-benito-van-der-zander/fizzbuzztick.hs',   'fizzbuzztick.hs',   'A compact FizzBuzz variant driven by time ticks.'),
        ('benito-helloworld',     'examples/2013-benito-van-der-zander/helloworld.hs',     'helloworld.hs',     'A fourth take on "Hello, World!" — generated from a HomeSpringTree source.'),
    ]),
]

HST_LINK = {
    'benito-clock':    'examples/2013-benito-van-der-zander/clock.hst',
    'benito-count':    'examples/2013-benito-van-der-zander/count.hst',
    'benito-count2':   'examples/2013-benito-van-der-zander/count2.hst',
    'benito-count3':   'examples/2013-benito-van-der-zander/count3.hst',
    'benito-count4':   'examples/2013-benito-van-der-zander/count4.hst',
    'benito-count-poem': 'examples/2013-benito-van-der-zander/count.poem.hst',
    'benito-fizzbuzz': 'examples/2013-benito-van-der-zander/fizzbuzz.hst',
    'benito-fizzbuzz-poem': 'examples/2013-benito-van-der-zander/fizzbuzz.poem.hst',
    'benito-fizzbuzztick': 'examples/2013-benito-van-der-zander/fizzbuzztick.hst',
    'benito-helloworld':   'examples/2013-benito-van-der-zander/helloworld.hst',
}

ADAPTERS = [
    '2003-jeff-binder',
    '2003-cal-henderson',
    '2005-joe-neeman',
    '2012-quin-kennedy',
    '2017-cal-henderson-js',
]

QUIN_STAR_SKIP = {'jeff-simple'}
PERL_DOUBLE_STAR = {'jeff-hello-1', 'jeff-hello-2'}


def is_disagreement_skip(note):
    s = (note or '').lower()
    return 'canonical' in s or 'disagreement' in s or 'nothing to verify' in s


def html_escape(s):
    return (s.replace('&', '&amp;').replace('"', '&quot;')
             .replace('<', '&lt;').replace('>', '&gt;'))


def cell(res, slug, aid):
    status = res['status'] if res else None
    note = (res.get('notes') or [''])[0] if res else ''
    presumed = bool(res.get('presumed_pass')) if res else False

    if status == 'pass':
        label = 'yes'
        if aid == '2012-quin-kennedy' and slug not in QUIN_STAR_SKIP:
            label = 'yes<sup>*</sup>'
        elif aid == '2003-cal-henderson' and slug in PERL_DOUBLE_STAR:
            label = 'yes<sup>&dagger;</sup>'
        return f'<td class="compat yes">{label}</td>'
    if status == 'skip' and presumed:
        # "Pass in spirit" — harness can't verify but the author's own
        # interpreter is known to run the program correctly.
        title = f' title="{html_escape(note)}"' if note else ''
        return f'<td class="compat yes presumed"{title}><i>yes</i></td>'
    if status == 'skip' and is_disagreement_skip(note):
        return '<td class="compat unknown">?</td>'
    # status is fail/skip/timeout/error — render "no" and attach the reason
    # (if we have one) as a tooltip.
    if note:
        return f'<td class="compat no" title="{html_escape(note)}">no</td>'
    return '<td class="compat no">no</td>'


def main():
    r = json.load(open(RESULTS))
    by_prog = {}
    for res in r['results']:
        by_prog.setdefault(res['program'], {})[res['adapter']] = res

    for i, (author, meta, progs) in enumerate(SECTIONS):
        if i > 0:
            print()
        print('\t\t<tr class="author-row">')
        print(f'\t\t\t<td colspan="7">{author} <span class="author-meta">{meta}</span></td>')
        print('\t\t</tr>')
        print()
        for j, (slug, href, name, desc) in enumerate(progs):
            name_cell = f'<a href="{href}">{name}</a>'
            if slug in HST_LINK:
                name_cell += f'<span class="src-alt">(<a href="{HST_LINK[slug]}">.hst</a>)</span>'
            cells = []
            for aid in ADAPTERS:
                res = by_prog.get(slug, {}).get(aid)
                if not res:
                    cells.append('<td class="compat unknown">?</td>')
                else:
                    cells.append(cell(res, slug, aid))
            print('\t\t<tr>')
            print(f'\t\t\t<td class="name">{name_cell}</td>')
            print(f'\t\t\t<td class="desc">{desc}</td>')
            for c in cells:
                print(f'\t\t\t{c}')
            print('\t\t</tr>')


if __name__ == '__main__':
    main()
