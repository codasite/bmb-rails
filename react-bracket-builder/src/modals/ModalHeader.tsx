export const ModalHeader = (props: { text: string }) => {
  return (
    <h1 className="tw-text-24 sm:tw-text-32 tw-leading-normal tw-text-white tw-uppercase tw-whitespace-pre-line tw-mb-30 tw-w-full tw-text-center tw-font-sans">
      {props.text}
    </h1>
  )
}
