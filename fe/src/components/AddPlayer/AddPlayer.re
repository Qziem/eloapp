open Svc;
open EloTypes;
[%bs.raw {|require('./AddPlayer.scss')|}];

type state = {
  code: string,
  name: string,
  warning: bool,
};

type action =
  | ChangeCode(string)
  | ChangeName(string)
  | SetWarning(bool)
  | AddPlayer(containerActions => unit);

let component = ReasonReact.reducerComponent("AddPlayer");

let initialState = () => {code: "", name: "", warning: false};

let addPlayerSvc = (state, containterSend) => {
  let payload =
    Json.Encode.object_([
      ("code", Json.Encode.string(state.code)),
      ("name", Json.Encode.string(state.name)),
    ]);
  ReasonReact.UpdateWithSideEffects(
    {...state, warning: false},
    _self =>
      Js.Promise.(
        svcPost("users", payload)
        |> then_(_result => containterSend(GetUsersSvc) |> resolve)
      )
      |> ignore,
  );
};

let addPlayerReducer = (state, containterSend) => {
  let codeValid = String.trim(state.code) !== "";
  let nameValid = String.trim(state.name) !== "";
  switch (codeValid, nameValid) {
  | (true, true) => addPlayerSvc(state, containterSend)
  | _ => ReasonReact.Update({...state, warning: true})
  };
};

let reducer = (action, state) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, code})
  | ChangeName(name) => ReasonReact.Update({...state, name})
  | SetWarning(warning) => ReasonReact.Update({...state, warning})
  | AddPlayer(containterSend) => addPlayerReducer(state, containterSend)
  };

let make = (~containterSend, _children) => {
  ...component,
  initialState,
  reducer,
  render: self =>
    <div className="addPlayer">
      {
        self.state.warning ?
          <div className="warning">
            {ReasonReact.string("Fields can not be empty")}
          </div> :
          ReasonReact.null
      }
      <form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            self.send(AddPlayer(containterSend));
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
                      self.send(
                        ChangeCode(GameResult.valueFromEvent(event)),
                      )
                  }
                />
              </td>
              <td>
                <input
                  placeholder="name"
                  onChange={
                    event =>
                      self.send(
                        ChangeName(GameResult.valueFromEvent(event)),
                      )
                  }
                />
              </td>
              <td> <button> {ReasonReact.string("Add")} </button> </td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>,
};