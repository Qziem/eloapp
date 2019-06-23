open Jest;

let () =
  describe(
    "FailureMask",
    ExpectJs.(
      () => {
        test("should render properly", () =>
          expect(<FailureMask />) |> toMatchSnapshot
        );
      }
    ),
  );