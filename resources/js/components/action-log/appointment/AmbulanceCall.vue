<script>
import DefaultLog from "@/components/action-log/Default.vue";
import ActionLogRepository from "@/repositories/action-log";

export default {
  extends: DefaultLog,
  props: {
    id: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      repository: new ActionLogRepository({
        filters: { ambulance_call: this.id },
      }),
      attributes: {
        caller: __("Вызвал"),
        phone: __("Контактный номер телефона"),
        street: __("Улица"),
        house: __("Дом"),
        district: __("Район"),
        porch: __("Подъезд"),
        flat: __("Квартира"),
        storey: __("Этаж"),
        reason: __("Повод вызова"),
        comment: __("Комментарий"),
        call_transferred_time: {
          label: __("Время передачи вызова"),
          formatter: (val) => {
             return this.$formatter.datetimeFormat(val);
          },
        },
        en_route_time: {
          label: __("Время выезда"),
          formatter: (val) => {
             return this.$formatter.datetimeFormat(val);
          },
        },
        arrival_time: {
          label: __("Время доезда"),
          formatter: (val) => {
             return this.$formatter.datetimeFormat(val);
          },
        },
        en_route_overall_minutes: {
          label: __("Время в пути"),
          formatter: (val) => {
             return this.$formatter.datetimeFormat(val);
          },
        },
        created_at: {
          label: __("Время создания"),
          formatter: (val) => {
             return this.$formatter.datetimeFormat(val);
          },
        },
      },
    };
  },
};
</script>
