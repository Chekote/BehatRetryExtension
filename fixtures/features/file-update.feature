Feature: Asynchronous file update

  Scenario: File is updated in one second with 0 second timeout
    Given the file "test_file.txt" contents are "some text"
    And the file "test_file.txt" contents will be "something else" in 1 seconds
    Then the file "test_file.txt" contents should be "something else"