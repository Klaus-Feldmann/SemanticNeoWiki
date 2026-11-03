<?php
// Isolated tests of unmodified SMW 7.3.1 parsers; not a running MediaWiki store.
namespace SMW\DataValues\ValueParsers { interface ValueParser {} }
namespace SMW\Localizer {
 class Localizer {
  public static function getInstance() { return new self; }
  public function getLang($code) { return new class { public function findMonthNumberByLabel($label) { return false; } }; }
 }
}
namespace {
 $base = dirname(__DIR__) . '/Pruefergebnisse/Quellen/';
 foreach (['Components.php','Timezone.php','TimeStringParser.php','TimeValueParser.php','SMW-ParserParameterProcessor.php'] as $file) require $base . $file;
 function verify($condition, $message) { if (!$condition) throw new \RuntimeException($message); }
 $parser = new \SMW\DataValues\ValueParsers\TimeValueParser;
 foreach (['427 BC Jl','347 BC Jl','28 August 1749 Gr','1 October 1458 Gr','12 February 2026 08:49:00+00:00 .000 Gr','11 February 2026 18:30:59+00:00 .123456789 Gr'] as $input) {
  $v=$parser->parse($input);
  verify($v !== false && !$parser->getErrors(), 'Cannot parse: '.$input);
 }
 $v=$parser->parse('11 February 2026 18:30:59+00:00 .123456789 Gr');
 verify($v->get('microseconds') === '123456789', 'Fraction changed');
 verify($v->get('calendarmodel') === 'Gr', 'Calendar changed');
 foreach (['https://example.org/?a=1&b=two#x', ']]{{NotATemplate}}|+sep=;&<text>', '0', 'a,b'] as $value) {
  $processor=new \SMW\ParserParameterProcessor(['Test='.$value]);
  verify($processor->toArray() === ['Test'=>[$value]], 'Structured argument changed: '.$value);
 }
 echo "12 checks of SMW 7.3.1 parser inputs passed.\n";
}
