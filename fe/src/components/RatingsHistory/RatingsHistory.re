open Svc;
open EloTypes;
open Helpers;
[%bs.raw {|require('./RatingsHistory.scss')|}];

type dataStateType =
  | INITIAL
  | LOADING
  | WARNING(string)
  | LOADED(list(ratingHistory));

type stateType = {
  inputCode: string,
  dataState: dataStateType,
  /* ratingsHistory: option(list(ratingHistory)),
     warning: bool,
     loading: bool, */
};

type action =
  | GetHistory(list(user))
  | SetHistory(list(ratingHistory))
  | ChangeCode(string);

let component = ReasonReact.reducerComponent("Stats");

let initialState = (): stateType => {inputCode: "", dataState: INITIAL};

let getHistorySvc = (state, users) => {
  let userNid = getUserNidFromCode(state.inputCode, users);

  ReasonReact.UpdateWithSideEffects(
    {...state, dataState: LOADING},
    self =>
      Js.Promise.(
        svcGet("ratings_history/" ++ string_of_int(userNid))
        |> then_(json =>
             DecodeRatingsHistory.ratingsHistoryDec(json) |> resolve
           )
        |> then_(result => self.send(SetHistory(result)) |> resolve)
      )
      |> ignore,
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
  };

let make = (~users, _children) => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="ratingsHistory">
      <form
        className="topBar"
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            send(GetHistory(users));
          }
        }>
        <table>
          <tbody>
            <tr>
              <td>
                <input
                  placeholder="code"
                  onChange={
                    event =>
                      send(ChangeCode(GameResult.valueFromEvent(event)))
                  }
                />
              </td>
              <td>
                <button
                  disabled={
                    switch (state.dataState) {
                    | LOADING => true
                    | _ => false
                    }
                  }>
                  {ReasonReact.string("Get stats")}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </form>
      {
        switch (state.dataState) {
        | INITIAL => ReasonReact.null
        | LOADING =>
          <div className="loadingMsg">
            {"Loading data" |> ReasonReact.string}
          </div>
        | WARNING(msg) =>
          <div className="warningMsg"> {msg |> ReasonReact.string} </div>
        | LOADED(ratingsHistory) => <RatingsHistoryTable ratingsHistory />
        }
      }
    </div>,
};