<?php

namespace Phlexy;

class LexerDataGenerator {
    public function getInfoFromRegexToTokenMap(array $regexToTokenMap) {
        $regexes = array_keys($regexToTokenMap);
        $tokens = array_values($regexToTokenMap);

        $compiledRegex = $this->getAllRegexesCompiledIntoOne($regexes);
        $offsetToLengthMap = $this->getOffsetToLengthMap($regexes);
        $offsetToTokenMap = array_combine(array_keys($offsetToLengthMap), $tokens);

        return array($compiledRegex, $offsetToTokenMap, $offsetToLengthMap);
    }

    public function getAllRegexesCompiledIntoOne(array $regexes) {
        return '~(' . str_replace('~', '\~', implode(')|(', $regexes)) . ')~A';
    }

    public function getOffsetToLengthMap(array $regexes) {
        $offsetToLengthMap = array();

        $currentOffset = 0;
        foreach ($regexes as $regex) {
            // We have to add +1 because the whole regex will also be made capturing
            $numberOfCapturingGroups = 1 + $this->getNumberOfCapturingGroupsInRegex($regex);

            $offsetToLengthMap[$currentOffset] = $numberOfCapturingGroups;
            $currentOffset += $numberOfCapturingGroups;
        }

        return $offsetToLengthMap;
    }

    protected function getNumberOfCapturingGroupsInRegex($regex) {
        // The regex to count the number of capturing groups should be fairly complete. The only thing I know it
        // won't work with are (?| ... ) groups.
        return preg_match_all(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \?
                    (?!
                        <(?![!=])
                      | P<
                      | \'
                    )
                  | \*
                )
            ~x',
            $regex, $dummyVar
        );
    }
}