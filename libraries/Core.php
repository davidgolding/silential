<?php

namespace libraries;

class Controller {
    
    public static function run($config = []) {
        $request  = new Request();
        $response = new Response();
        
        //fetch endpoint::action and pass params along
        $params     = $request->params;
        $controller = empty($params['controller']) ? 'app' : $params['controller'];
        $action     = empty($params['action']) ? 'index' : $params['action'];
        
        foreach (['controller', 'action'] as $param) {
            if (isset($params[$param])) {
                unset($params[$param]);
            }
        }
        $args = $params;
        $class = Inflector::camelize($controller . '_controller');
        
        //catch dead-end requests
        if (!file_exists(dirname(__DIR__) . '/controllers/' . $class . '.php')) {
            $response->status = 404;
            $response->type = 'json';
            return $response->render(['message' => 'Request not found.']);
        }
        
        if (!is_callable(['controllers\\' . $class, $action])) {
            $response->status = 404;
            $response->type = 'json';
            return $response->render(['message' => 'Requested action not found.']);
        }
        
        //route request to controller action
        $method = Text::insert('controllers\\{:class}::{:action}', compact('class', 'action'));
        return call_user_func($method, compact('args', 'request', 'response'));
    }
}

/**
 * Utility for modifying format of words. Change singular to plural and vice versa.
 * Under_score a CamelCased word and vice versa. Replace spaces and special characters.
 * Create a human readable word from the others. Used when consistency in naming
 * conventions must be enforced.
 */
class Inflector {

	/**
	 * Contains a default map of accented and special characters to ASCII characters.  Can be
	 * extended or added to using `Inflector::rules()`.
	 *
	 * @see lithium\util\Inflector::slug()
	 * @see lithium\util\Inflector::rules()
	 * @var array
	 */
	protected static $_transliteration = [
		'/à|á|å|â/' => 'a',
		'/Á|À|Å|Â/' => 'A',
		'/è|é|ė|ê|ẽ|ë/' => 'e',
		'/É|È|Ė|Ê|Ē|Ë/' => 'E',
		'/ì|í|î/' => 'i',
		'/Í|Ì|Î/' => 'I',
		'/ò|ó|ơ|ô|ø/' => 'o',
		'/Ò|Ó|Ơ|Ô|Ø/' => 'O',
		'/ù|ú|ů|û/' => 'u',
		'/Ú|Ù|Ů|Û/' => 'U',
		'/ç|ć|č/' => 'c',
		'/Č|Ć|Č/' => 'C',
		'/đ/' => 'dj',
		'/Đ/' => 'Dj',
		'/DŽ/' => 'Dz',
		'/š/' => 's',
		'/Š/' => 'S',
		'/ž/' => 'z',
		'/Ž/' => 'Z',
		'/ñ/' => 'n',
		'/Ñ/' => 'N',
		'/ä|æ/' => 'ae',
		'/Ä/' => 'Ae',
		'/ö/' => 'oe',
		'/Ö/' => 'Oe',
		'/ü/' => 'ue',
		'/Ü/' => 'Ue',
		'/ß/' => 'ss'
	];

	/**
	 * Indexed array of words which are the same in both singular and plural form.  You can add
	 * rules to this list using `Inflector::rules()`.
	 *
	 * @see lithium\util\Inflector::rules()
	 * @var array
	 */
	protected static $_uninflected = [
		'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
		'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
		'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
		'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
		'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
		'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media',
		'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese', 'People',
		'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
		'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
		'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'swine', 'testes',
		'trousers', 'trout','tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest',
		'Yengeese'
	];

	/**
	 * Contains the list of pluralization rules.
	 *
	 * @see lithium\util\Inflector::rules()
	 * @var array Contains the following keys:
	 *      - `'rules'`: An array of regular expression rules in the form of
	 *        `'match' => 'replace'`, which specify the matching and replacing rules for
	 *        the pluralization of words.
	 *      - `'uninflected'`: A indexed array containing regex word patterns which do not
	 *        get inflected (i.e. singular and plural are the same).
	 *      - `'irregular'`: Contains key-value pairs of specific words which are
	 *        not inflected according to the rules. This is populated from `Inflector::$_plural`
	 *        when the class is loaded.
	 */
	protected static $_singular = [
		'rules' => [
			'/(s)tatuses$/i' => '\1\2tatus',
			'/^(.*)(menu)s$/i' => '\1\2',
			'/(quiz)zes$/i' => '\\1',
			'/(matr)ices$/i' => '\1ix',
			'/(vert|ind)ices$/i' => '\1ex',
			'/^(ox)en/i' => '\1',
			'/(alias)(es)*$/i' => '\1',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(shoe)s$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/ouses$/' => 'ouse',
			'/([^a])uses$/' => '\1us',
			'/([m|l])ice$/i' => '\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\1',
			'/(m)ovies$/i' => '\1\2ovie',
			'/(s)eries$/i' => '\1\2eries',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/([lr])ves$/i' => '\1f',
			'/(tive)s$/i' => '\1',
			'/(hive)s$/i' => '\1',
			'/(drive)s$/i' => '\1',
			'/([^fo])ves$/i' => '\1fe',
			'/(^analy)ses$/i' => '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/([ti])a$/i' => '\1um',
			'/(p)eople$/i' => '\1\2erson',
			'/(m)en$/i' => '\1an',
			'/(c)hildren$/i' => '\1\2hild',
			'/(n)ews$/i' => '\1\2ews',
			'/^(.*us)$/' => '\\1',
			'/s$/i' => ''
		],
		'irregular' => [],
		'uninflected' => [
			'.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'
		]
	];

	/**
	 * Contains a cache map of previously singularized words.
	 *
	 * @var array
	 */
	protected static $_singularized = [];

	/**
	 * Contains the list of pluralization rules.
	 *
	 * @see lithium\util\Inflector::rules()
	 * @var array Contains the following keys:
	 *      - `'rules'`: An array of regular expression rules in the form of
	 *        `'match' => 'replace'`, which specify the matching and replacing
	 *        rules for the pluralization of words.
	 *      - `'uninflected'`: A indexed array containing regex word patterns
	 *        which do not get inflected (i.e. singular and plural are the same).
	 *      - `'irregular'`: Contains key-value pairs of specific words which are
	 *        not inflected according to the rules.
	 */
	protected static $_plural = [
		'rules' => [
			'/(s)tatus$/i' => '\1\2tatuses',
			'/(quiz)$/i' => '\1zes',
			'/^(ox)$/i' => '\1\2en',
			'/([m|l])ouse$/i' => '\1ice',
			'/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',
			'/(x|ch|ss|sh)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(hive)$/i' => '\1s',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/sis$/i' => 'ses',
			'/([ti])um$/i' => '\1a',
			'/(p)erson$/i' => '\1eople',
			'/(m)an$/i' => '\1en',
			'/(c)hild$/i' => '\1hildren',
			'/(buffal|tomat)o$/i' => '\1\2oes',
			'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
			'/us$/' => 'uses',
			'/(alias)$/i' => '\1es',
			'/(ax|cri|test)is$/i' => '\1es',
			'/s$/' => 's',
			'/^$/' => '',
			'/$/' => 's'
		],
		'irregular' => [
			'atlas' => 'atlases', 'beef' => 'beefs', 'brother' => 'brothers',
			'child' => 'children', 'corpus' => 'corpuses', 'cow' => 'cows',
			'ganglion' => 'ganglions', 'genie' => 'genies', 'genus' => 'genera',
			'graffito' => 'graffiti', 'hoof' => 'hoofs', 'loaf' => 'loaves', 'man' => 'men',
			'leaf' => 'leaves', 'money' => 'monies', 'mongoose' => 'mongooses', 'move' => 'moves',
			'mythos' => 'mythoi', 'numen' => 'numina', 'occiput' => 'occiputs',
			'octopus' => 'octopuses', 'opus' => 'opuses', 'ox' => 'oxen', 'penis' => 'penises',
			'person' => 'people', 'sex' => 'sexes', 'sleeve' => 'sleeves',
			'soliloquy' => 'soliloquies', 'tax' => 'taxes', 'testis' => 'testes',
			'trilby' => 'trilbys', 'turf' => 'turfs'
		],
		'uninflected' => [
			'.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep'
		]
	];

	/**
	 * Contains a cache map of previously pluralized words.
	 *
	 * @var array
	 */
	protected static $_pluralized = [];

	/**
	 * Contains a cache map of previously camelized words.
	 *
	 * @var array
	 */
	protected static $_camelized = [];

	/**
	 * Contains a cache map of previously underscored words.
	 *
	 * @var array
	 */
	protected static $_underscored = [];

	/**
	 * Contains a cache map of previously humanized words.
	 *
	 * @var array
	 */
	protected static $_humanized = [];

	/**
	 * Clears local in-memory caches.  Can be used to force a full-cache clear when updating
	 * inflection rules mid-way through request execution.
	 */
	public static function reset() {
		static::$_singularized = static::$_pluralized = [];
		static::$_camelized = static::$_underscored = [];
		static::$_humanized = [];

		static::$_plural['regexUninflected'] = static::$_singular['regexUninflected'] = null;
		static::$_plural['regexIrregular'] = static::$_singular['regexIrregular'] = null;
		static::$_transliteration = [
			'/à|á|å|â/' => 'a',
			'/è|é|ê|ẽ|ë/' => 'e',
			'/ì|í|î/' => 'i',
			'/ò|ó|ô|ø/' => 'o',
			'/ù|ú|ů|û/' => 'u',
			'/ç|ć|č/' => 'c',
			'/đ/' => 'dj',
			'/š/' => 's',
			'/ž/' => 'z',
			'/ñ/' => 'n',
			'/ä|æ/' => 'ae',
			'/ö/' => 'oe',
			'/ü/' => 'ue',
			'/Ä/' => 'Ae',
			'/Ü/' => 'Ue',
			'/Ö/' => 'Oe',
			'/ß/' => 'ss',
			'/Č|Ć/' => 'C',
			'/DŽ/' => 'Dz',
			'/Đ/' => 'Dj',
			'/Š/' => 'S',
			'/Ž/' => 'Z'
		];
	}

	/**
	 * Gets or adds inflection and transliteration rules.
	 *
	 * @param string $type Either `'transliteration'`, `'uninflected'`, `'singular'` or `'plural'`.
	 * @param array $config
	 * @return mixed If `$config` is empty, returns the rules list specified
	 *         by `$type`, otherwise returns `null`.
	 */
	public static function rules($type, $config = []) {
		$var = '_' . $type;

		if (!isset(static::${$var})) {
			return null;
		}
		if (empty($config)) {
			return static::${$var};
		}
		switch ($type) {
			case 'transliteration':
				$_config = [];

				foreach ($config as $key => $val) {
					if ($key[0] !== '/') {
						$key = '/' . join('|', array_filter(preg_split('//u', $key))) . '/';
					}
					$_config[$key] = $val;
				}
				static::$_transliteration = array_merge(
					$_config, static::$_transliteration, $_config
				);
			break;
			case 'uninflected':
				static::$_uninflected = array_merge(static::$_uninflected, (array) $config);
				static::$_plural['regexUninflected'] = null;
				static::$_singular['regexUninflected'] = null;

				foreach ((array) $config as $word) {
					unset(static::$_singularized[$word], static::$_pluralized[$word]);
				}
			break;
			case 'singular':
			case 'plural':
				if (isset(static::${$var}[key($config)])) {
					foreach ($config as $rType => $set) {
						static::${$var}[$rType] = array_merge($set, static::${$var}[$rType], $set);

						if ($rType === 'irregular') {
							$swap = ($type === 'singular' ? '_plural' : '_singular');
							static::${$swap}[$rType] = array_flip(static::${$var}[$rType]);
						}
					}
				} else {
					static::${$var}['rules'] = array_merge(
						$config, static::${$var}['rules'], $config
					);
				}
			break;
		}
	}

	/**
	 * Changes the form of a word from singular to plural.
	 *
	 * @param string $word Word in singular form.
	 * @return string Word in plural form.
	 */
	public static function pluralize($word) {
		if (isset(static::$_pluralized[$word])) {
			return static::$_pluralized[$word];
		}
		extract(static::$_plural);

		if (!isset($regexUninflected) || !isset($regexIrregular)) {
			$regexUninflected = static::_enclose(join('|', $uninflected + static::$_uninflected));
			$regexIrregular = static::_enclose(join('|', array_keys($irregular)));
			static::$_plural += compact('regexUninflected', 'regexIrregular');
		}
		if (preg_match('/(' . $regexUninflected . ')$/i', $word, $regs)) {
			return static::$_pluralized[$word] = $word;
		}
		if (preg_match('/(.*)\\b(' . $regexIrregular . ')$/i', $word, $regs)) {
			$plural = substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
			return static::$_pluralized[$word] = $regs[1] . $plural;
		}
		foreach ($rules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return static::$_pluralized[$word] = preg_replace($rule, $replacement, $word);
			}
		}
		return static::$_pluralized[$word] = $word;
	}

	/**
	 * Changes the form of a word from plural to singular.
	 *
	 * @param string $word Word in plural form.
	 * @return string Word in singular form.
	 */
	public static function singularize($word) {
		if (isset(static::$_singularized[$word])) {
			return static::$_singularized[$word];
		}
		if (empty(static::$_singular['irregular'])) {
			static::$_singular['irregular'] = array_flip(static::$_plural['irregular']);
		}
		extract(static::$_singular);

		if (!isset($regexUninflected) || !isset($regexIrregular)) {
			$regexUninflected = static::_enclose(join('|', $uninflected + static::$_uninflected));
			$regexIrregular = static::_enclose(join('|', array_keys($irregular)));
			static::$_singular += compact('regexUninflected', 'regexIrregular');
		}
		if (preg_match("/(.*)\\b({$regexIrregular})\$/i", $word, $regs)) {
			$singular = substr($word, 0, 1) . substr($irregular[strtolower($regs[2])], 1);
			return static::$_singularized[$word] = $regs[1] . $singular;
		}
		if (preg_match('/^(' . $regexUninflected . ')$/i', $word, $regs)) {
			return static::$_singularized[$word] = $word;
		}
		foreach ($rules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return static::$_singularized[$word] = preg_replace($rule, $replacement, $word);
			}
		}
		return static::$_singularized[$word] = $word;
	}

	/**
	 * Takes a under_scored word and turns it into a CamelCased or camelBack word
	 *
	 * @param string $word An under_scored or slugged word (i.e. `'red_bike'` or `'red-bike'`).
	 * @param boolean $cased If false, first character is not upper cased
	 * @return string CamelCased version of the word (i.e. `'RedBike'`).
	 */
	public static function camelize($word, $cased = true) {
		$_word = $word;

		if (isset(static::$_camelized[$_word]) && $cased) {
			return static::$_camelized[$_word];
		}
		$word = str_replace(" ", "", ucwords(str_replace(["_", '-'], " ", $word)));

		if (!$cased) {
			return lcfirst($word);
		}
		return static::$_camelized[$_word] = $word;
	}

	/**
	 * Takes a CamelCased version of a word and turns it into an under_scored one.
	 *
	 * @param string $word CamelCased version of a word (i.e. `'RedBike'`).
	 * @return string Under_scored version of the workd (i.e. `'red_bike'`).
	 */
	public static function underscore($word) {
		if (isset(static::$_underscored[$word])) {
			return static::$_underscored[$word];
		}
		return static::$_underscored[$word] = strtolower(static::slug($word, '_'));
	}

	/**
	 * Returns a string with all spaces converted to given replacement and
	 * non word characters removed.  Maps special characters to ASCII using
	 * `Inflector::$_transliteration`, which can be updated using `Inflector::rules()`.
	 *
	 * @see lithium\util\Inflector::rules()
	 * @param string $string An arbitrary string to convert.
	 * @param string $replacement The replacement to use for spaces.
	 * @return string The converted string.
	 */
	public static function slug($string, $replacement = '-') {
		$map = static::$_transliteration + [
			'/[^\s\p{L}\p{Nd}]/u' => ' ',
			'/\s+/u' => $replacement,
			'/(?<=[a-z])([A-Z])/' => $replacement . '\\1',
			str_replace(':rep', preg_quote($replacement, '/'), '/^[:rep]+|[:rep]+$/') => ''
		];
		return preg_replace(array_keys($map), array_values($map), $string);
	}

	/**
	 * Takes an under_scored version of a word and turns it into an human- readable form
	 * by replacing underscores with a space, and by upper casing the initial character.
	 *
	 * @param string $word Under_scored version of a word (i.e. `'red_bike'`).
	 * @param string $separator The separator character used in the initial string.
	 * @return string Human readable version of the word (i.e. `'Red Bike'`).
	 */
	public static function humanize($word, $separator = '_') {
		if (isset(static::$_humanized[$key = $word . ':' . $separator])) {
			return static::$_humanized[$key];
		}
		return static::$_humanized[$key] = ucwords(str_replace($separator, " ", $word));
	}

	/**
	 * Takes a CamelCased class name and returns corresponding under_scored table name.
	 *
	 * @param string $className CamelCased class name (i.e. `'Post'`).
	 * @return string Under_scored and plural table name (i.e. `'posts'`).
	 */
	public static function tableize($className) {
		return static::pluralize(static::underscore($className));
	}

	/**
	 * Takes a under_scored table name and returns corresponding class name.
	 *
	 * @param string $tableName Under_scored and plural table name (i.e. `'posts'`).
	 * @return string CamelCased class name (i.e. `'Post'`).
	 */
	public static function classify($tableName) {
		return static::camelize(static::singularize($tableName));
	}

	/**
	 * Enclose a string for preg matching.
	 *
	 * @param string $string String to enclose
	 * @return string Enclosed string
	 */
	protected static function _enclose($string) {
		return '(?:' . $string . ')';
	}
}

/**
 * A cryptographically-strong random number generator, which allows to generate arbritrary
 * length strings of bytes, usable for i.e. password salts, UUIDs, keys or initialization
 * vectors (IVs). Random byte strings can be encoded using the base64-encoder for use with
 * DES and XDES.
 */
class Random {

	/**
	 * Option flag for the encoder.
	 *
	 * @see lithium\security\Random::generate()
	 */
	const ENCODE_BASE_64 = 1;

	/**
	 * A callable which, given a number of bytes, returns that
	 * amount of random bytes.
	 *
	 * @see lithium\security\Random::_source()
	 * @var callable
	 */
	protected static $_source;

	/**
	 * Generates random bytes for use in UUIDs and password salts, using
	 * a cryptographically strong random number generator source.
	 *
	 * ```
	 * $bits = Random::generate(8); // 64 bits
	 * $hex = bin2hex($bits); // [0-9a-f]+
	 * ```
	 *
	 * Optionally base64-encodes the resulting random string per the following. The
	 * alphabet used by `base64_encode()` is different than the one we should be using.
	 * When considering the meaty part of the resulting string, however, a bijection
	 * allows to go the from one to another. Given that we're working on random bytes, we
	 * can use safely use `base64_encode()` without losing any entropy.
	 *
	 * @param integer $bytes The number of random bytes to generate.
	 * @param array $options The options used when generating random bytes:
	 *              - `'encode'` _integer_: If specified, and set to `Random::ENCODE_BASE_64`, the
	 *                resulting value will be base64-encoded, per the note above.
	 * @return string Returns (an encoded) string of random bytes.
	 */
	public static function generate($bytes, array $options = []) {
		$defaults = ['encode' => null];
		$options += $defaults;

		$source = static::$_source ?: (static::$_source = static::_source());
		$result = $source($bytes);

		if ($options['encode'] !== static::ENCODE_BASE_64) {
			return $result;
		}
		return strtr(rtrim(base64_encode($result), '='), '+', '.');
	}

	/**
	 * Returns the best available random number generator source.
	 *
	 * The source of randomness used are as follows:
	 *
	 * 1. `random_bytes()`, available in PHP >=7.0
	 * 2. `random_bytes()`, available if the openssl extension is installed
	 * 3. `mcrypt_create_iv()`, available if the mcrypt extensions is installed
	 * 4. `/dev/urandom`, available on *nix
	 * 5. `GetRandom()` through COM, available on Windows
	 *
	 * Note: Users restricting path access through the `open_basedir` INI setting,
	 * will need to include `/dev/urandom` into the list of allowed paths, as this
	 * method might read from it.
	 *
	 * @link http://php.net/random_bytes
	 * @link http://php.net/mcrypt_create_iv
	 * @link http://msdn.microsoft.com/en-us/library/aa388182%28VS.85%29.aspx?ppud=4
	 * @link http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
	 * @see lithium\util\Random::$_source
	 * @return callable Returns a closure containing a random number generator.
	 */
	protected static function _source() {
		if (function_exists('random_bytes')) {
			return function($bytes) {
				return random_bytes($bytes);
			};
		}
		if (function_exists('openssl_random_pseudo_bytes')) {
			return function($bytes) {
				return openssl_random_pseudo_bytes($bytes);
			};
		}
		if (function_exists('mcrypt_create_iv')) {
			return function($bytes) {
				return mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
			};
		}
		if (is_readable('/dev/urandom')) {
			return function($bytes) {
				$stream = fopen('/dev/urandom', 'rb');
				$result = fread($stream, $bytes);
				fclose($stream);

				return $result;
			};
		}
		if (class_exists('COM', false)) {
			$com = new COM('CAPICOM.Utilities.1');

			return function($bytes) use ($com) {
				return base64_decode($com->GetRandom($bytes, 0));
			};
		}
		throw new LogicException('No suitable strong random number generator source found.');
	}
}

class Request {
    
    public $url = null;
    
    public $params = [];
    
    public $data = [];
    
    public $query = [];
    
    public $method = 'GET';
    
    public $auth = null;
    
    public $host = 'localhost';
    
    public $port = null;
    
    public $username = null;
    
    public $password = null;
    
    public $path = null;
    
    public $body = null;
    
    public $scheme = 'tcp';
    
    public $persist = [];
    
    public $routes = [];
    
    protected $_computed = [];
    
    protected $_base = null;
    
    protected $_env = [];
    
    protected $_stream = null;
    
    protected $_formats = [];
    
    protected $_detectors = [
		'mobile'  => ['HTTP_USER_AGENT', null],
		'ajax'    => ['HTTP_X_REQUESTED_WITH', 'XMLHttpRequest'],
		'flash'   => ['HTTP_USER_AGENT', 'Shockwave Flash'],
		'ssl'     => 'HTTPS',
		'dnt'     => ['HTTP_DNT', '1'],
		'get'     => ['REQUEST_METHOD', 'GET'],
		'post'    => ['REQUEST_METHOD', 'POST'],
		'patch'   => ['REQUEST_METHOD', 'PATCH'],
		'put'     => ['REQUEST_METHOD', 'PUT'],
		'delete'  => ['REQUEST_METHOD', 'DELETE'],
		'head'    => ['REQUEST_METHOD', 'HEAD'],
		'options' => ['REQUEST_METHOD', 'OPTIONS']
	];
    
    /**
     * Constructor method
     */
    public function __construct(array $config = []) {
        if (!defined('ROUTES')) {
            define('ROUTES', 'controller/action/args');
        }
        $defaults = [
            'base' => null,
            'url' => null,
            'env' => [],
            'data' => [],
            'stream' => null,
            'query' => [],
            'headers' => [],
            'type' => null,
            'routes' => explode('/', ROUTES)
        ];
        $config += $defaults;
        
        if (isset($_SERVER)) {
            $config['env'] += $_SERVER;
        }
        if (isset($_ENV)) {
            $config['env'] += $_ENV;
        }
        if (isset($_GET)) {
            $config['query'] += $_GET;
        }
        if (isset($_POST)) {
            $config['data'] += $_POST;
        }
        
        $this->_env = $config['env'];
        
        if (!isset($config['host'])) {
			$config['host'] = $this->env('HTTP_HOST');
		}
		if (!isset($config['protocol'])) {
			$config['protocol'] = $this->env('SERVER_PROTOCOL');
		}
		$this->_base = $this->_base($config['base']);
		$this->url = $this->_url($config['url']);
		$this->routes = $config['routes'];

		$config['headers'] += [
			'Content-Type' => $this->env('CONTENT_TYPE'),
			'Content-Length' => $this->env('CONTENT_LENGTH')
		];

		foreach ($this->_env as $name => $value) {
			if ($name[0] === 'H' && strpos($name, 'HTTP_') === 0) {
				$name = str_replace('_', ' ', substr($name, 5));
				$name = str_replace(' ', '-', ucwords(strtolower($name)));
				$config['headers'] += [$name => $value];
			}
		}
		$this->_config($config);
		
		$this->headers = [
			'Host' => $this->port ? "{$this->host}:{$this->port}" : $this->host,
			'Connection' => 'Close',
			'User-Agent' => 'Mozilla/5.0'
		];
		foreach (['type', 'headers'] as $field) {
			if ($value = $this->_config[$field]) {
				$this->{$field}($value);
			}
		}
		
		$this->_formats += [
			'url' => function($req, $options) {
				$options['port'] = $options['port'] ? ":{$options['port']}" : '';
				$options['path'] = str_replace('//', '/', $options['path']);

				return Text::insert("{:scheme}://{:host}{:port}{:path}{:query}", $options);
			},
			'context' => function($req, $options, $defaults) {
				$req->headers($options['headers']);

				return ['http' => array_diff_key($options, $defaults) + [
					'content' => $req->body(),
					'method' => $options['method'],
					'header' => $req->headers(),
					'protocol_version' => $options['version'],
					'ignore_errors' => $options['ignore_errors'],
					'follow_location' => $options['follow_location'],
					'request_fulluri' => $options['request_fulluri'],
					'proxy' => $options['proxy']
				]];
			},
			'string' => function($req, $options) {
				$body = $req->body();
				$path = str_replace('//', '/', $options['path']) . $options['query'];
				$status = "{$options['method']} {$path} {$req->protocol}";

				return join("\r\n", [$status, join("\r\n", $req->headers()), "", $body]);
			}
		];
		
		$this->data = (array) $config['data'];

		if (isset($this->data['_method'])) {
			$this->_computed['HTTP_X_HTTP_METHOD_OVERRIDE'] = strtoupper($this->data['_method']);
			unset($this->data['_method']);
		}
		$type = $this->type($config['type'] ?: $this->env('CONTENT_TYPE'));
		$this->method = strtoupper($this->env('REQUEST_METHOD'));
    }
    
    /**
     * Additional configurations
     */
    protected function _config($config) {
        $defaults = [
			'method' => 'GET',
			'query' => [],
			'cookies' => [],
			'type' => null,
			'auth' => null,
			'proxy' => null,
			'ignoreErrors' => true,
			'followLocation' => true
		];
		$config += $defaults;
		$this->method  = $config['method'];
		$this->query   = $config['query'];
		$this->auth    = $config['auth'];
		$this->data    = $config['data'];
		$this->params = $this->_params();
    }
    
    protected function _params() {
        $nodes = explode('/', ltrim($this->url, '/'));
        $params = [];
        for ($i = 0; $i < count($nodes); $i++) {
            $params[$this->routes[$i]] = $nodes[$i];
        }
        return $params;
    }
    
    /**
     * Type
     */
    public function type($type = null) {
		if (!$type && !empty($this->params['type'])) {
			$type = $this->params['type'];
		}
		return $type;
	}
    
    /**
     * Getter for params
     */
    public function __get($name) {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
    }
    
    /**
     * Magic method for retrieving class params with a function
     */
    public function __isset($name) {
		return isset($this->params[$name]);
	}
	
	/**
     * Returns env keys with some additional formatting
     */
	public function env($key) {
		if (array_key_exists($key, $this->_computed)) {
			return $this->_computed[$key];
		}
		$val = null;

		if (!empty($this->_env[$key])) {
			$val = $this->_env[$key];
			if ($key !== 'REMOTE_ADDR' && $key !== 'HTTPS' && $key !== 'REQUEST_METHOD') {
				return $this->_computed[$key] = $val;
			}
		}
		switch ($key) {
			case 'BASE':
			case 'base':
				$val = $this->_base($this->_config['base']);
			break;
			case 'HTTP_HOST':
				$val = 'localhost';
			break;
			case 'SERVER_PROTOCOL':
				$val = 'HTTP/1.1';
			break;
			case 'REQUEST_METHOD':
				if ($this->env('HTTP_X_HTTP_METHOD_OVERRIDE')) {
					$val = $this->env('HTTP_X_HTTP_METHOD_OVERRIDE');
				} elseif (isset($this->_env['REQUEST_METHOD'])) {
					$val = $this->_env['REQUEST_METHOD'];
				} else {
					$val = 'GET';
				}
			break;
			case 'CONTENT_TYPE':
				$val = 'text/html';
			break;
			case 'PLATFORM':
				$envs = ['isapi' => 'IIS', 'cgi' => 'CGI', 'cgi-fcgi' => 'CGI'];
				$val = isset($envs[PHP_SAPI]) ? $envs[PHP_SAPI] : null;
			break;
			case 'REMOTE_ADDR':
				$https = [
					'HTTP_X_FORWARDED_FOR',
					'HTTP_PC_REMOTE_ADDR',
					'HTTP_X_REAL_IP'
				];
				foreach ($https as $altKey) {
					if ($addr = $this->env($altKey)) {
						list($val) = explode(', ', $addr);
						break;
					}
				}
			break;
			case 'SCRIPT_NAME':
				if ($this->env('PLATFORM') === 'CGI') {
					return $this->env('SCRIPT_URL');
				}
				$val = null;
			break;
			case 'HTTPS':
				if (isset($this->_env['SCRIPT_URI'])) {
					$val = strpos($this->_env['SCRIPT_URI'], 'https://') === 0;
				} elseif (isset($this->_env['HTTPS'])) {
					$val = (!empty($this->_env['HTTPS']) && $this->_env['HTTPS'] !== 'off');
				} else {
					$val = false;
				}
			break;
			case 'SERVER_ADDR':
				if (empty($this->_env['SERVER_ADDR']) && !empty($this->_env['LOCAL_ADDR'])) {
					$val = $this->_env['LOCAL_ADDR'];
				} elseif (isset($this->_env['SERVER_ADDR'])) {
					$val = $this->_env['SERVER_ADDR'];
				}
			break;
			case 'SCRIPT_FILENAME':
				if ($this->env('PLATFORM') === 'IIS') {
					$val = str_replace('\\\\', '\\', $this->env('PATH_TRANSLATED'));
				} elseif (isset($this->_env['DOCUMENT_ROOT']) && isset($this->_env['PHP_SELF'])) {
					$val = $this->_env['DOCUMENT_ROOT'] . $this->_env['PHP_SELF'];
				}
			break;
			case 'DOCUMENT_ROOT':
				$fileName = $this->env('SCRIPT_FILENAME');
				$offset = (!strpos($this->env('SCRIPT_NAME'), '.php')) ? 4 : 0;
				$offset = strlen($fileName) - (strlen($this->env('SCRIPT_NAME')) + $offset);
				$val = substr($fileName, 0, $offset);
			break;
			case 'PHP_SELF':
				$val = '/';
			break;
			case 'CGI':
			case 'CGI_MODE':
				$val = $this->env('PLATFORM') === 'CGI';
			break;
			case 'HTTP_BASE':
				$val = preg_replace('/^([^.])*/i', null, $this->env('HTTP_HOST'));
			break;
			case 'PHP_AUTH_USER':
			case 'PHP_AUTH_PW':
			case 'PHP_AUTH_DIGEST':
				if (!$header = $this->env('HTTP_AUTHORIZATION')) {
					if (!$header = $this->env('REDIRECT_HTTP_AUTHORIZATION')) {
						return $this->_computed[$key] = $val;
					}
				}
				if (stripos($header, 'basic') === 0) {
					$decoded = base64_decode(substr($header, strlen('basic ')));

					if (strpos($decoded, ':') !== false) {
						list($user, $password) = explode(':', $decoded, 2);

						$this->_computed['PHP_AUTH_USER'] = $user;
						$this->_computed['PHP_AUTH_PW'] = $password;
						return $this->_computed[$key];
					}
				} elseif (stripos($header, 'digest') === 0) {
					return $this->_computed[$key] = substr($header, strlen('digest '));
				}
			default:
				$val = array_key_exists($key, $this->_env) ? $this->_env[$key] : $val;
			break;
		}
		return $this->_computed[$key] = $val;
	}
	
	/**
     * Get the base of the URL
     */
	protected function _base($base = null) {
		if ($base === null) {
			$base = preg_replace('/[^\/]+$/', '', $this->env('PHP_SELF'));
		}
		$base = trim(str_replace(["/app/webroot", '/webroot'], '', $base), '/');
		return $base ? '/' . $base : '';
	}
	
	/**
     * Get additional values
     */
	public function get($key) {
		list($var, $key) = explode(':', $key);

		switch (true) {
			case in_array($var, ['params', 'data', 'query']):
				return isset($this->{$var}[$key]) ? $this->{$var}[$key] : null;
			case ($var === 'env'):
				return $this->env(strtoupper($key));
			case ($var === 'http' && $key === 'method'):
				return $this->env('REQUEST_METHOD');
			case ($var === 'http'):
				return $this->env('HTTP_' . strtoupper($key));
		}
	}
	
	/**
     * Trim the URL to get nodes from request
     */
	protected function _url($url = null) {
		if ($url !== null) {
			return '/' . trim($url, '/');
		} elseif ($uri = $this->env('REQUEST_URI')) {
			list($uri) = explode('?', $uri, 2);
			$base = '/^' . preg_quote($this->_base, '/') . '/';
			return '/' . trim(preg_replace($base, '', $uri), '/') ?: '/';
		}
		return '/';
	}
	
	/**
     * Returns the referer
     */
	public function referer($default = null, $local = false) {
		if ($ref = $this->env('HTTP_REFERER')) {
			if (!$local) {
				return $ref;
			}
			$url = parse_url($ref) + ['path' => ''];
			if (empty($url['host']) || $url['host'] === $this->env('HTTP_HOST')) {
				$ref = $url['path'];
				if (!empty($url['query'])) {
					$ref .= '?' . $url['query'];
				}
				if (!empty($url['fragment'])) {
					$ref .= '#' . $url['fragment'];
				}
				return $ref;
			}
		}
		return ($default !== null) ? $default : '/';
	}
}

class Response {
    
    public $type = null;
    
    public $methods = 'GET';
    
    public $charset = null;
    
    public $allowHeaders = null;
    
    public $maxAge = 3600;
    
    public $allowOrigin = '*';
    
    public $headers = [];
    
    public $status = 401;
    
    public $headerKeys = [
        'Access-Control-Allow-Origin'  => 'allowOrigin',
        'Content-Type'                 => 'contentType',
        'Access-Control-Allow-Methods' => 'allowMethods',
        'Access-Control-Max-Age'       => 'maxAge',
        'Access-Control-Allow-Headers' => 'allowHeaders'
    ];
    
    public $types = [
        'html' => ['text/html', 'application/xhtml+xml', '*/*'],
        'htm'  => ['alias' => 'html'],
        'form' => ['application/x-www-form-urlencoded', 'multipart/form-data'],
        'json' => ['application/json'],
        'rss'  => ['application/rss+xml'],
        'atom' => ['application/atom+xml'],
        'css'  => ['text/css'],
        'js'   => ['application/javascript', 'text/javascript'],
        'text' => ['text/plain'],
        'txt'  => ['alias' => 'text'],
        'xml'  => ['application/xml', 'application/soap+xml', 'text/xml']
    ];
    
    public $statuses = [
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        401 => 'HTTP/1.1 401 Unauthorized',
        404 => 'HTTP/1.1 404 Not Found',
        422 => 'HTTP/1.1 422 Unprocessable Entity'
    ];
    
    public function __construct($config = []) {
        $defaults = [
            'type' => 'text',
            'methods' => ['get', 'post'],
            'charset' => 'UTF-8',
            'allowHeaders' => [
                'Content-Type',
                'Access-Control-Allow-Headers',
                'Authorization',
                'X-Requested-With'
            ],
            'maxAge' => 3600,
            'allowOrigin' => '*',
            'headers' => []
        ];
        $config += $defaults;
        $this->type = reset($this->types[$config['type']]);
        $this->methods = strtoupper(join(',', $config['methods']));
        $this->charset = $config['charset'];
        $this->allowHeaders = join(', ', $config['allowHeaders']);
        $this->maxAge = $config['maxAge'];
        $this->allowOrigin = $config['allowOrigin'];
        $this->headers = $this->_headers($config['headers']);
    }
    
    protected function _headers($headers = []) {
        $out = [];
        foreach ($this->headerKeys as $key => $method) {
            if (isset($headers[$key])) {
                $out[$key] = $headers[$key];
                continue;
            }
            $out[$key] = $this->{$method}($key);
        }
        return $out;
    }
    
    public function allowOrigin($key = 'Access-Control-Allow-Origin') {
        return $key . ': ' . $this->allowOrigin;
    }
    
    public function contentType($key = 'Content-Type') {
        return $key . ': ' . $this->type . '; charset=' . $this->charset;
    }
    
    public function allowMethods($key = 'Access-Control-Allow-Methods') {
        return $key . ': ' . $this->methods;
    }
    
    public function maxAge($key = 'Access-Control-Max-Age') {
        return $key . ': ' . $this->maxAge;
    }
    
    public function allowHeaders($key = 'Access-Control-Allow-Headers') {
        return $key . ': ' . $this->allowHeaders;
    }
    
    public function render($data = [], $status = null) {
        foreach ($this->headers as $string) {
            header($string);
        }
        $_status = is_null($status) ? $this->status : $status;
        header($this->statuses[$_status]);
        $out = $this->type == 'json' ? json_encode($data) : $data;
        echo $out;
    }
}

/**
 * Text manipulation utility class. Includes functionality for generating UUIDs,
 * {:tag} and regex replacement, and tokenization.
 */
class Text {

	/**
	 * UUID-related constant. Clears all bits of version byte (`00001111`).
	 */
	const UUID_CLEAR_VER = 15;

	/**
	 * UUID constant that sets the version bit for generated UUIDs (`01000000`).
	 */
	const UUID_VERSION_4 = 64;

	/**
	 * Clears relevant bits of variant byte (`00111111`).
	 */
	const UUID_CLEAR_VAR = 63;

	/**
	 * The RFC 4122 variant (`10000000`).
	 */
	const UUID_VAR_RFC = 128;

	/**
	 * Generates an RFC 4122-compliant version 4 UUID.
	 *
	 * @return string The string representation of an RFC 4122-compliant, version 4 UUID.
	 * @link http://www.ietf.org/rfc/rfc4122.txt RFC 4122: UUID URN Namespace
	 */
	public static function uuid() {
		$uuid = Random::generate(16);
		$uuid[6] = chr(ord($uuid[6]) & static::UUID_CLEAR_VER | static::UUID_VERSION_4);
		$uuid[8] = chr(ord($uuid[8]) & static::UUID_CLEAR_VAR | static::UUID_VAR_RFC);

		return join('-', [
			bin2hex(substr($uuid, 0, 4)),
			bin2hex(substr($uuid, 4, 2)),
			bin2hex(substr($uuid, 6, 2)),
			bin2hex(substr($uuid, 8, 2)),
			bin2hex(substr($uuid, 10, 6))
		]);
	}

	/**
	 * Replaces variable placeholders inside a string with any given data. Each key
	 * in the `$data` array corresponds to a variable placeholder name in `$str`.
	 *
	 * Usage:
	 * ```
	 * Text::insert(
	 *     'My name is {:name} and I am {:age} years old.',
	 *     ['name' => 'Bob', 'age' => '65']
	 * ); // returns 'My name is Bob and I am 65 years old.'
	 * ```
	 *
	 * Please note that optimization have applied to this method and parts of the code
	 * may look like it can refactored or removed but in fact this is part of the applied
	 * optimization. Please check the history for this section of code before refactoring
	 *
	 * @param string $str A string containing variable place-holders.
	 * @param array $data A key, value array where each key stands for a place-holder variable
	 *                     name to be replaced with value.
	 * @param array $options Available options are:
	 *        - `'after'`: The character or string after the name of the variable place-holder
	 *          (defaults to `}`).
	 *        - `'before'`: The character or string in front of the name of the variable
	 *          place-holder (defaults to `'{:'`).
	 *        - `'clean'`: A boolean or array with instructions for `Text::clean()`.
	 *        - `'escape'`: The character or string used to escape the before character or string
	 *          (defaults to `'\'`).
	 *        - `'format'`: A regular expression to use for matching variable place-holders
	 *          (defaults to `'/(?<!\\)\:%s/'`. Please note that this option takes precedence over
	 *          all other options except `'clean'`.
	 * @return string
	 */
	public static function insert($str, array $data, array $options = []) {
		$defaults = [
			'before' => '{:',
			'after' => '}',
			'escape' => null,
			'format' => null,
			'clean' => false
		];
		$options += $defaults;
		$format = $options['format'];

		if ($format === 'regex' || (!$format && $options['escape'])) {
			$format = sprintf(
				'/(?<!%s)%s%%s%s/',
				preg_quote($options['escape'], '/'),
				str_replace('%', '%%', preg_quote($options['before'], '/')),
				str_replace('%', '%%', preg_quote($options['after'], '/'))
			);
		}

		if (!$format && key($data) !== 0) {
			$replace = [];

			foreach ($data as $key => $value) {
				if (!is_scalar($value)) {
					if (is_object($value) && method_exists($value, '__toString')) {
						$value = (string) $value;
					} else {
						$value = '';
					}
				}
				$replace["{$options['before']}{$key}{$options['after']}"] = $value;
			}
			$str = strtr($str, $replace);
			return $options['clean'] ? static::clean($str, $options) : $str;
		}

		if (strpos($str, '?') !== false && isset($data[0])) {
			$offset = 0;

			while (($pos = strpos($str, '?', $offset)) !== false) {
				$val = array_shift($data);
				$offset = $pos + strlen($val);
				$str = substr_replace($str, $val, $pos, 1);
			}
			return $options['clean'] ? static::clean($str, $options) : $str;
		}

		foreach ($data as $key => $value) {
			if (!$key = sprintf($format, preg_quote($key, '/'))) {
				continue;
			}
			$hash = crc32($key);

			$str = preg_replace($key, $hash, $str);
			$str = str_replace($hash, $value, $str);
		}

		if (!isset($options['format']) && isset($options['before'])) {
			$str = str_replace($options['escape'] . $options['before'], $options['before'], $str);
		}
		return $options['clean'] ? static::clean($str, $options) : $str;
	}

	/**
	 * Cleans up a `Text::insert()` formatted string with given `$options` depending
	 * on the `'clean'` option. The goal of this function is to replace all whitespace
	 * and unneeded mark-up around place-holders that did not get replaced by `Text::insert()`.
	 *
	 * @param string $str The string to clean.
	 * @param array $options Available options are:
	 *        - `'after'`: characters marking the end of targeted substring.
	 *        - `'andText'`: (defaults to `true`).
	 *        - `'before'`: characters marking the start of targeted substring.
	 *        - `'clean'`: `true` or an array of clean options:
	 *          - `'gap'`: Regular expression matching gaps.
	 *          - `'method'`: Either `'text'` or `'html'` (defaults to `'text'`).
	 *          - `'replacement'`: Text to use for cleaned substrings (defaults to `''`).
	 *          - `'word'`: Regular expression matching words.
	 * @return string The cleaned string.
	 */
	public static function clean($str, array $options = []) {
		if (is_array($options['clean'])) {
			$clean = $options['clean'];
		} else {
			$clean = [
				'method' => is_bool($options['clean']) ? 'text' : $options['clean']
			];
		}

		switch ($clean['method']) {
			case 'text':
				$clean += [
					'word' => '[\w,.]+',
					'gap' => '[\s]*(?:(?:and|or|,)[\s]*)?',
					'replacement' => ''
				];
				$before = preg_quote($options['before'], '/');
				$after = preg_quote($options['after'], '/');

				$kleenex = sprintf(
					'/(%s%s%s%s|%s%s%s%s|%s%s%s%s%s)/',
					$before, $clean['word'], $after, $clean['gap'],
					$clean['gap'], $before, $clean['word'], $after,
					$clean['gap'], $before, $clean['word'], $after, $clean['gap']
				);
				$str = preg_replace($kleenex, $clean['replacement'], $str);
			break;
			case 'html':
				$clean += [
					'word' => '[\w,.]+',
					'andText' => true,
					'replacement' => ''
				];
				$kleenex = sprintf(
					'/[\s]*[a-z]+=(")(%s%s%s[\s]*)+\\1/i',
					preg_quote($options['before'], '/'),
					$clean['word'],
					preg_quote($options['after'], '/')
				);
				$str = preg_replace($kleenex, $clean['replacement'], $str);

				if ($clean['andText']) {
					return static::clean($str, [
						'clean' => ['method' => 'text']
					] + $options);
				}
			break;
		}
		return $str;
	}

	/**
	 * Extract a part of a string based on a regular expression `$regex`.
	 *
	 * @param string $regex The regular expression to use.
	 * @param string $str The string to run the extraction on.
	 * @param integer $index The number of the part to return based on the regex.
	 * @return mixed
	 */
	public static function extract($regex, $str, $index = 0) {
		if (!preg_match($regex, $str, $match)) {
			return false;
		}
		return isset($match[$index]) ? $match[$index] : null;
	}

	/**
	 * Tokenizes a string using `$options['separator']`, ignoring any instances of
	 * `$options['separator']` that appear between `$options['leftBound']` and
	 * `$options['rightBound']`.
	 *
	 * @param string $data The data to tokenize.
	 * @param array $options Options to use when tokenizing:
	 *              -`'separator'` _string_: The token to split the data on.
	 *              -`'leftBound'` _string_: Left scope-enclosing boundary.
	 *              -`'rightBound'` _string_: Right scope-enclosing boundary.
	 * @return array Returns an array of tokens.
	 */
	public static function tokenize($data, array $options = []) {
		$options += ['separator' => ',', 'leftBound' => '(', 'rightBound' => ')'];

		if (!$data || is_array($data)) {
			return $data;
		}

		$depth = 0;
		$offset = 0;
		$buffer = '';
		$results = [];
		$length = strlen($data);
		$open = false;

		while ($offset <= $length) {
			$tmpOffset = -1;
			$offsets = [
				strpos($data, $options['separator'], $offset),
				strpos($data, $options['leftBound'], $offset),
				strpos($data, $options['rightBound'], $offset)
			];

			for ($i = 0; $i < 3; $i++) {
				if ($offsets[$i] !== false && ($offsets[$i] < $tmpOffset || $tmpOffset === -1)) {
					$tmpOffset = $offsets[$i];
				}
			}

			if ($tmpOffset === -1) {
				$results[] = $buffer . substr($data, $offset);
				$offset = $length + 1;
				continue;
			}
			$buffer .= substr($data, $offset, ($tmpOffset - $offset));

			if ($data[$tmpOffset] === $options['separator'] && $depth === 0) {
				$results[] = $buffer;
				$buffer = '';
			} else {
				$buffer .= $data{$tmpOffset};
			}

			if ($options['leftBound'] !== $options['rightBound']) {
				if ($data[$tmpOffset] === $options['leftBound']) {
					$depth++;
				}
				if ($data[$tmpOffset] === $options['rightBound']) {
					$depth--;
				}
				$offset = ++$tmpOffset;
				continue;
			}

			if ($data[$tmpOffset] === $options['leftBound']) {
				($open) ? $depth-- : $depth++;
				$open = !$open;
			}
			$offset = ++$tmpOffset;
		}

		if (!$results && $buffer) {
			$results[] = $buffer;
		}
		return $results ? array_map('trim', $results) : [];
	}
}

spl_autoload_register(function ($class) {
    $path = dirname(__DIR__) . '/' . str_replace("\\", '/', $class) . '.php';
    include($path);
});

?>