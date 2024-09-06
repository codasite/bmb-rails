export const PlayScore = (props: { scorePercent: number; label: string }) => {
  return (
    <div className="tw-flex tw-flex-col tw-items-end tw-gap-4 tw-leading-none">
      <span className="tw-text-10 sm:tw-text-12 tw-font-700 tw-text-green">
        {props.label}
      </span>
      <span className="tw-text-36 sm:tw-text-48 tw-font-700 tw-text-green">
        {Math.round(props.scorePercent)}%
      </span>
    </div>
  )
}
