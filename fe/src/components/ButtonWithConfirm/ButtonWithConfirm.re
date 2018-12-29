[%bs.raw {|require('./ButtonWithConfirm.scss')|}];
open BsReactstrap;

type state =
  | INITIAL
  | CONFIRM;

type action =
  | SetConfirm
  | SetInitial;

let component = ReasonReact.reducerComponent("ButtonWithConfirm");

let initialState = () => INITIAL;

let reducer = (action, _state) =>
  switch (action) {
  | SetConfirm => ReasonReact.Update(CONFIRM)
  | SetInitial => ReasonReact.Update(INITIAL)
  };

let make = (~label, ~disabled, ~onYesClick, _children) => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="buttonWithConfirm">
      {
        switch (state) {
        | INITIAL =>
          <Button
            color="primary" disabled onClick=(_event => send(SetConfirm))>
            {label |> ReasonReact.string}
          </Button>
        | CONFIRM =>
          <div className="confirmation">
            <div className="sureLabel">
              {"Are you sure?" |> ReasonReact.string}
            </div>
            <Button
              color="primary"
              className="yesButton"
              onClick=(
                _event => {
                  onYesClick();
                  send(SetInitial);
                }
              )>
              {"Yes" |> ReasonReact.string}
            </Button>
            <Button
              color="secondary"
              className="noButton"
              onClick=(_event => send(SetInitial))>
              {"No" |> ReasonReact.string}
            </Button>
          </div>
        }
      }
    </div>,
};