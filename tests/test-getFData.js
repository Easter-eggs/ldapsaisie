var tests=[
// array(format, test val, test good result)
['%{toto:2}', 'abcdef', 'ab'],
['%{toto:3:-2}', 'abcdef', 'bc'],
['%{toto:1:0}', 'abcdef', 'bcdef'],
['%{toto:-2}', 'abcdef', 'ef'],
['%{toto:-3:2}', 'abcdef', 'de'],
['%{toto:-1}', 'abcdef', 'f'],
['%{toto!}', '<a>tiTé', '<A>TITÉ'],
['%{toto_}', '<a>tiTé', '<a>tité'],
['%{toto~}', '<a>tiTé', '<a>tiTe'],
['%{toto%}', '<a>tiTé', '&#60;a&#62;tiT&#233;'],
['%{toto!%}', '<a>tiTé', '&#60;A&#62;TIT&#201;'],
['%{toto!~}', '<a>tiTé', '<A>TITE'],
['%{toto!~%}', '<a>tiTé', '&#60;A&#62;TITE'],
['%{toto:1!%}', '<a>tiTé', '&#60;'],
['%{toto:1:0!~}', '<a>tiTé', 'A>TITE'],
['%{toto:-3!~%}', '<a>tiTé', 'ITE'],
['%{toto:-3:2!~%}', '<a>tiTé', 'IT'],
];

var nb_tests = tests.length;
for (i = 0; i < nb_tests; i++) {
        var result = getFData(tests[i][0], tests[i][1]);
        var ok = 'OK';
        if (result != tests[i][2]) {
          ok = "\n\t!!!! NOK !!!!";
        }
        console.log('Test ('+i+') : "'+tests[i][0]+'" ('+tests[i][2]+') : "'+tests[i][1]+'" -> "'+result+'" => '+ok);
}
