open Svc;
open EloTypes;
open Js.Promise;
open BsReactstrap;

[%bs.raw {|require('./RatingsHistory.scss')|}];

type dataStateType =
  | INITIAL
  | LOADING
  | FAILURE
  | WARNING(string)
  | LOADED(list(ratingHistory));

type stateType = {
  inputCode: string,
  dataState: dataStateType,
};

type action =
  | GetHistory
  | SetHistory(list(ratingHistory))
  | ChangeCode(string)
  | SetFailure
  | SetWarning(string)
  | ClearDataState;

let component = ReasonReact.reducerComponent("Stats");

let initialState = (): stateType => {inputCode: "", dataState: INITIAL};

let onSuccess = (send, json) => {
  let ratingsHistoryResult = DecodeRatingsHistory.response(json);
  switch (ratingsHistoryResult) {
  | WARNING(msg) => send(SetWarning(msg))
  | SUCCESS([]) => send(SetWarning("Player has no games"))
  | SUCCESS(ratingsHistoryResult) => send(SetHistory(ratingsHistoryResult))
  };
};

let onError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let getHistorySvc = (state, code) =>
  ReasonReact.UpdateWithSideEffects(
    {...state, dataState: LOADING},
    ({send}) => {
      let url = "ratings_history/" ++ code;
      svcGet(url)
      |> then_(json => onSuccess(send, json) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
      |> ignore;
    },
  );

let getHistoryReducer = state =>
  switch (String.trim(state.inputCode)) {
  | "" =>
    ReasonReact.SideEffects(
      (({send}) => send(SetWarning("Code can not be empty"))),
    )
  | code => getHistorySvc(state, code)
  };

let reducer = (action, state: stateType) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, inputCode: code})
  | GetHistory => getHistoryReducer(state)
  | SetHistory(ratingsHistory) =>
    ReasonReact.Update({...state, dataState: LOADED(ratingsHistory)})
  | SetFailure => ReasonReact.Update({...state, dataState: FAILURE})
  | SetWarning(msg) =>
    ReasonReact.Update({...state, dataState: WARNING(msg)})
  | ClearDataState => ReasonReact.Update({...state, dataState: INITIAL})
  };

let hanldeDismissAlert = (send, ()) => send(ClearDataState);

let make = (~disable, _children) => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="ratingsHistory">
      <Form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            send(GetHistory);
          }
        }>
        <Container>
          <Row>
            <Col xs=4>
              <FormGroup>
                <Input
                  placeholder="code"
                  onChange={
                    event =>
                      send(ChangeCode(GameResult.valueFromEvent(event)))
                  }
                />
              </FormGroup>
            </Col>
            <Col>
              <Button
                color="primary"
                disabled={
                  switch (disable, state.dataState) {
                  | (true, _) => true
                  | (false, LOADING) => true
                  | (false, _) => false
                  }
                }>
                {ReasonReact.string("Get stats")}
              </Button>
            </Col>
          </Row>
        </Container>
      </Form>
      {
        switch (state.dataState) {
        | INITIAL => ReasonReact.null
        | LOADING => <LoadingMask />
        | WARNING(msg) =>
          <Alert color="warning" toggle={hanldeDismissAlert(send)}>
            {msg |> ReasonReact.string}
          </Alert>
        | LOADED(ratingsHistory) => <RatingsHistoryTable ratingsHistory />
        | FAILURE => <FailureMask />
        }
      }
    </div>,
};