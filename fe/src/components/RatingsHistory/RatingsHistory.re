open Svc;
open EloTypes;
open Helpers;
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
  | GetHistory(list(user))
  | SetHistory(list(ratingHistory))
  | ChangeCode(string)
  | SetFailure
  | ClearDataState;

let component = ReasonReact.reducerComponent("Stats");

let initialState = (): stateType => {inputCode: "", dataState: INITIAL};

let onSuccess = (send, json) =>
  json
  |> DecodeRatingsHistory.ratingsHistoryDec
  |> (ratingsHistory => send(SetHistory(ratingsHistory)));

let onError = (send, err) => {
  send(SetFailure);
  Js.Console.error(err);
};

let getHistorySvc = (state, users) => {
  let userNid = getUserNidFromCode(state.inputCode, users);

  ReasonReact.UpdateWithSideEffects(
    {...state, dataState: LOADING},
    ({send}) => {
      let url = "ratings_history/" ++ string_of_int(userNid);
      svcGet(url)
      |> then_(json => onSuccess(send, json) |> resolve)
      |> catch(err => onError(send, err) |> resolve)
      |> ignore;
    },
  );
};

let getHistoryReducer = (state: stateType, users: list(user)) => {
  let userExist =
    List.exists(user => compareCodes(user.code, state.inputCode), users);

  userExist ?
    getHistorySvc(state, users) :
    ReasonReact.Update({
      ...state,
      dataState: WARNING("Player doesn't exist"),
    });
};

let reducer = (action, state: stateType) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, inputCode: code})
  | GetHistory(users) => getHistoryReducer(state, users)
  | SetHistory(ratingsHistory) =>
    ReasonReact.Update({...state, dataState: LOADED(ratingsHistory)})
  | SetFailure => ReasonReact.Update({...state, dataState: FAILURE})
  | ClearDataState => ReasonReact.Update({...state, dataState: INITIAL})
  };

let hanldeDismissAlert = (send, ()) => send(ClearDataState);

let make = (~users, ~disable, _children) => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="ratingsHistory">
      <Form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            send(GetHistory(users));
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