open Svc;
open EloTypes;
open Helpers;
[%bs.raw {|require('./Stats.scss')|}];

type state = {
  ratingsHistory: list(ratingHistory),
  inputCode: string,
  warning: bool,
};

type action =
  | GetHistory(list(user))
  | SetHistory(list(ratingHistory))
  | ChangeCode(string);

let component = ReasonReact.reducerComponent("Stats");

let initialState = () => {ratingsHistory: [], inputCode: "", warning: false};

let getHistorySvc = (state, users) => {
  let userNid = getUserNidFromCode(state.inputCode, users);
  let payload =
    Json.Encode.object_([("userNid", Json.Encode.int(userNid))]);

  ReasonReact.UpdateWithSideEffects(
    {...state, warning: false},
    self =>
      Js.Promise.(
        svcPost("ratings_history", payload)
        |> then_(json =>
             DecodeRatingsHistory.ratingsHistoryDec(json) |> resolve
           )
        |> then_(result => self.send(SetHistory(result)) |> resolve)
      )
      |> ignore,
  );
};

let getHistoryReducer = (state: state, users: list(user)) => {
  let userExist =
    List.exists(user => compareCodes(user.code, state.inputCode), users);

  userExist ?
    getHistorySvc(state, users) :
    ReasonReact.Update({...state, warning: true});
};

let reducer = (action, state) =>
  switch (action) {
  | GetHistory(users) => getHistoryReducer(state, users)
  | ChangeCode(code) => ReasonReact.Update({...state, inputCode: code})
  | SetHistory(ratingsHistory) =>
    ReasonReact.Update({...state, ratingsHistory})
  };

let make = (~users, _children) => {
  ...component,
  initialState,
  reducer,
  render: self =>
    <div className="stats">
      {
        self.state.warning ?
          <div className="warning">
            {ReasonReact.string("Player doesn't exist")}
          </div> :
          ReasonReact.null
      }
      <form
        className="topBar"
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(GetHistory(users));
          }
        }>
        <input
          placeholder="code"
          onChange={
            event => self.send(ChangeCode(GameResult.valueFromEvent(event)))
          }
        />
        <button> {ReasonReact.string("Get stats")} </button>
      </form>
    </div>,
};